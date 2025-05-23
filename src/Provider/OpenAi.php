<?php

namespace Lenorix\Ai\Provider;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Lenorix\Ai\Chat\CoreChatCompletionResponse;
use Lenorix\Ai\Chat\CoreMessage;
use Lenorix\Ai\Chat\CoreMessageRole;
use Lenorix\Ai\Chat\CoreTool;

class OpenAi implements ChatCompletion
{
    private Client $client;

    public function __construct(
        public string $model,
        protected string $apiKey,
        protected ?string $organizationId = null,
        protected ?string $projectId = null,
        public string $baseUrl = 'https://api.openai.com/',
        public int $timeout = 30,
    ) {
        $headers = [
            'Authorization' => 'Bearer '.$apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];

        if (! is_null($this->organizationId)) {
            $headers['OpenAI-Organization'] = $this->organizationId;
        }
        if (! is_null($this->projectId)) {
            $headers['OpenAI-Project'] = $this->projectId;
        }

        $this->client = new Client([
            'base_uri' => $baseUrl,
            'headers' => $headers,
            'timeout' => $timeout,
        ]);
    }

    /**
     * @param  CoreTool[]  $tools
     * @param  CoreMessage[]  $messages  Previous messages to be sent for previous context.
     * @param  string|null  $prompt  Add user prompt as new message (included in messages in `CoreChatCompleteResponse`).
     * @param  string|null  $system  If there is not a system prompt as first message sent, this will be used without add it to new messages.
     * @param  float|null  $temperature  Temperature to use for the model (0.0 to 2.0, default is 1.0).
     * @param  int|null  $maxSteps  Maximum number of steps to execute tool calls (disabled by default).
     *
     * @throws GuzzleException
     * @throws \JsonException
     */
    public function generate(
        array $tools = [],
        array $messages = [],
        ?string $prompt = null,
        ?string $system = null,
        ?float $temperature = null,
        ?int $maxSteps = null,
        ?string $toolChoice = null
    ): CoreChatCompletionResponse {
        $messages = array_map(
            fn ($m) => $m instanceof CoreMessage ? $m->toArray() : $m,
            $messages
        );

        $toolsByName = [];
        foreach ($tools as $tool) {
            $toolsByName[$tool->name()] = $tool;
        }

        $tools = array_map(
            fn ($t) => $t instanceof CoreTool ? $t->toArray() : $t,
            $tools
        );

        if (count($messages) === 0 || $messages[0]['role'] !== 'system') {
            // NOTE: This is required to avoid API HTTP 400 error.
            array_unshift($messages, [
                'role' => CoreMessageRole::SYSTEM->value,
                'content' => $system ?? '',
            ]);
        }

        $newMessages = [];
        if ($prompt) {
            $message = [
                'role' => CoreMessageRole::USER->value,
                'content' => $prompt,
            ];
            $newMessages[] = $message;
            $messages[] = $message;
        }

        $totalTokens = 0;
        $promptTokens = 0;
        $completionTokens = 0;
        $cacheHitTokens = 0;

        // TODO: Simplify this logic and the whole method.
        $totalSteps = 0;
        $response = null;
        $originalToolChoice = $toolChoice;
        do {
            $toolChoice = $originalToolChoice;
            if ($response && array_key_exists('tool_calls', $response['choices'][0]['message'])) {
                if ($toolChoice === 'required') {
                    $toolChoice = 'auto';
                }
                $toolCalls = $response['choices'][0]['message']['tool_calls'] ?? [];
                foreach ($toolCalls as $toolCall) {
                    // TODO: Refactor this to CoreTool class.
                    $tool = $toolsByName[$toolCall['function']['name']];
                    $parameters = json_decode($toolCall['function']['arguments'],
                        associative: true,
                        flags: JSON_THROW_ON_ERROR
                    );
                    $result = $tool->execute(...$parameters);
                    $message = [
                        'role' => CoreMessageRole::TOOL->value,
                        'tool_call_id' => $toolCall['id'],
                        'content' => json_encode($result, JSON_THROW_ON_ERROR),
                    ];
                    // Until here. Need to think a name for the method.
                    // Also, this requires improve a Language Model Specification
                    // like https://ai-sdk.dev/docs/foundations/providers-and-models

                    $newMessages[] = $message;
                    $messages[] = $message;
                }
            } elseif ($totalSteps > 0) {
                break;
            }
            if ($maxSteps !== null && $totalSteps >= $maxSteps) {
                break;
            }

            $payload = $this->payload($messages, $tools, $toolChoice);

            if ($temperature) {
                $payload['temperature'] = $temperature;
            }

            $response = $this->sendChatCompletion($payload);

            $message = $response['choices'][0]['message'];
            $newMessages[] = CoreMessage::fromArray($message);
            $messages[] = $message;

            $totalTokens += $response['usage']['total_tokens'] ?? 0;
            $promptTokens += $response['usage']['prompt_tokens'] ?? 0;
            $completionTokens += $response['usage']['completion_tokens'] ?? 0;
            $cacheHitTokens += $response['usage']['cache_hit_tokens'] ?? 0;

            $totalSteps += 1;
        } while ($maxSteps && $totalSteps < $maxSteps);

        $newMessages = array_map(
            fn ($m) => is_array($m) ? CoreMessage::fromArray($m) : $m,
            $newMessages
        );

        return new CoreChatCompletionResponse(
            $response,
            messages: $newMessages,
            totalTokens: $totalTokens,
            promptTokens: $promptTokens,
            completionTokens: $completionTokens,
            cacheHitTokens: $cacheHitTokens,
        );
    }

    protected function payload(array $messages, array $tools, ?string $toolChoice = null): array
    {
        $toolChoiceDefault = (count($tools) > 0) ? 'auto' : 'none';

        return [
            'model' => $this->model,
            'messages' => $messages,
            'tools' => $tools,
            'tool_choice' => $toolChoice ?: $toolChoiceDefault,
        ];
    }

    /**
     * @throws \JsonException|GuzzleException
     */
    protected function sendChatCompletion(array $payload): array
    {
        return json_decode(
            $this->client->post('chat/completions', [
                'json' => $payload,
            ])->getBody()->getContents(),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );
    }
}
