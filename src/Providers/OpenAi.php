<?php

namespace Lenorix\Ai\Providers;

use GuzzleHttp\Client;
use Lenorix\Ai\Chat\CoreChatResponse;
use Lenorix\Ai\Chat\CoreTool;
use Lenorix\Ai\Provider\ChatCompletion;

class OpenAi implements ChatCompletion
{
    private Client $client;

    public function __construct(
        public string $model,
        public string $apiKey,
        public string $organization,
        public string $baseUrl = 'https://api.openai.com/',
        public int $timeout = 30,
    ) {
        $this->client = new Client([
            'base_uri' => $baseUrl,
            'headers' => [
            ],
            'timeout' => $timeout,
        ]);
    }

    public function generate(array $tools = [], array $messages = [], ?string $system = null,): CoreChatResponse
    {
        $messages = array_map(
            fn($m) => $m instanceof CoreMessage ? $m->toArray() : $m,
            $messages
        );
        $tools = array_map(
            fn($t) => $t instanceof CoreTool ? $t->toArray() : $t,
            $tools
        );

        $payload = [
            'model' => $this->model,
            'messages' => $messages,
            'tools' => $tools,
            'tool_choice' => $tools ? 'auto' : 'none',
        ];

        $response = $this->client->post('chat/completions', ['json' => $payload]);

        return new CoreChatResponse(
            json_decode($response->getBody()->getContents(), true, flags: JSON_THROW_ON_ERROR)
        );
    }
}
