<?php

namespace Lenorix\Ai\Provider;

use Psr\Log\LoggerInterface;

class DeepSeek extends OpenAi implements ChatCompletion
{
    public function __construct(
        public string $model,
        public string $apiKey,
        public string $baseUrl = 'https://api.deepseek.com/',
        public int $timeout = 30,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct(
            $model,
            $apiKey,
            baseUrl: $baseUrl,
            timeout: $timeout,
            logger: $logger
        );
    }
}
