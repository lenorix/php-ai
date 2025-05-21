<?php

namespace Lenorix\Ai\Providers;

use Lenorix\Ai\Chat\CoreChatCompletionResponse;
use Lenorix\Ai\Provider\ChatCompletion;

class DeepSeek extends OpenAi implements ChatCompletion
{
    public function __construct(
        public string $model,
        public string $apiKey,
        public string $baseUrl = 'https://api.deepseek.com/',
        public int $timeout = 30,
    ) {
        parent::__construct(
            $model,
            $apiKey,
            baseUrl: $baseUrl,
            timeout: $timeout,
        );
    }
}
