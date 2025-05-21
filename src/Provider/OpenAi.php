<?php

namespace Lenorix\Ai\Provider;

use GuzzleHttp\Client;
use Lenorix\Ai\Chat\CoreChatCompletionResponse;
use Lenorix\Ai\Chat\CoreMessage;
use Lenorix\Ai\Chat\CoreTool;

class OpenAi implements ChatCompletion
{
    private Client $client;

    private array $newMessages = [];

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
        ?string $system = null
    ): CoreChatCompletionResponse
    {
        $messages = array_map(
            fn ($m) => $m instanceof CoreMessage ? $m->toArray() : $m,
            $messages
        );
        $this->newMessages = [];

        if (count($messages) === 0 || $messages[0]['role'] !== 'system') {
            // NOTE: This is required to avoid API HTTP 400 error.
            array_unshift($messages, [
                'role' => 'system',
                'content' => $system ?? '',
            ]);
        }

        $tools = array_map(
            fn ($t) => $t instanceof CoreTool ? $t->toArray() : $t,
            $tools
        );

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'tools' => $tools,
            'tool_choice' => (count($tools) > 0) ? 'auto' : 'none',
        ];

        $response = json_decode(
            $this->client->post('chat/completions', ['json' => $payload])->getBody()->getContents(),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );
        $this->newMessages[] = CoreMessage::fromArray($response['choices'][0]['message']);

        return $this->chatCompletionResponse($response);
    }

    protected function chatCompletionResponse(array $response): CoreChatCompletionResponse
    {
        return new CoreChatCompletionResponse(
            $response,
            messages: $this->newMessages,
            totalTokens: $response['usage']['total_tokens'],
            promptTokens: $response['usage']['prompt_tokens'],
            completionTokens: $response['usage']['completion_tokens'],
            cacheHitTokens: $response['usage']['prompt_tokens_details']['cached_tokens'],
        );
    }
}
