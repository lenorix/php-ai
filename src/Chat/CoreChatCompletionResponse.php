<?php

namespace Lenorix\Ai\Chat;

class CoreChatCompletionResponse
{
    public function __construct(
        public mixed $response,

        /** @var CoreMessage[] */
        public array $messages,

        public int $totalTokens = 0,
        public int $promptTokens = 0,
        public int $completionTokens = 0,
        public int $cacheHitTokens = 0,
    ) {}
}
