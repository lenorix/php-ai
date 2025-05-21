<?php

namespace Lenorix\Ai\Provider;

use GuzzleHttp\Client;
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

    public function generate(
        array $tools = [],
        array $messages = [],
        ?string $system = null,
        ?int $maxSteps = null
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
                'role' => 'system',
                'content' => $system ?? '',
            ]);
        }

        $newMessages = [];
        $totalTokens = 0;
        $promptTokens = 0;
        $completionTokens = 0;
        $cacheHitTokens = 0;

        $totalSteps = 0;
        $response = null;
        do {
            if ($response && $response['choices'][0]['message']['tool_calls'] ?? null) {
                $toolCalls = $response['choices'][0]['message']['tool_calls'];
                foreach ($toolCalls as $toolCall) {
                    $tool = $toolsByName[$toolCall['function']['name']];
                    $parameters = json_decode($toolCall['function']['arguments'], true);
                    $result = $tool->execute(...$parameters);
                    $message = [
                        'role' => CoreMessageRole::TOOL,
                        'tool_call_id' => $toolCall['id'],
                        'content' => $result,
                    ];
                    $newMessages[] = $message;
                    $messages[] = $message;
                }
            }

            $response = $this->sendChatCompletion($this->payload($messages, $tools));

            $message = $response['choices'][0]['message'];
            $newMessages[] = CoreMessage::fromArray($message);
            $messages[] = $message;

            $totalTokens += $response['usage']['total_tokens'] ?? 0;
            $promptTokens += $response['usage']['prompt_tokens'] ?? 0;
            $completionTokens += $response['usage']['completion_tokens'] ?? 0;
            $cacheHitTokens += $response['usage']['cache_hit_tokens'] ?? 0;

            $totalSteps += 1;
        } while ($maxSteps && $totalSteps < $maxSteps);

        return new CoreChatCompletionResponse(
            $response,
            messages: $newMessages,
            totalTokens: $totalTokens,
            promptTokens: $promptTokens,
            completionTokens: $completionTokens,
            cacheHitTokens: $cacheHitTokens,
        );
    }

    protected function payload(array $messages, array $tools): array
    {
        return [
            'model' => $this->model,
            'messages' => $messages,
            'tools' => $tools,
            'tool_choice' => (count($tools) > 0) ? 'auto' : 'none',
        ];
    }

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
