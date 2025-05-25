<?php

namespace Lenorix\Ai\Chat;

class CoreChatCompletionResponse
{
    /**
     * @param  mixed  $response  Latest response from the provider API.
     * @param  CoreMessage[]  $messages  New messages created with chat completion.
     * @param  int  $totalTokens  Tokens usage from response statistics.
     */
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
