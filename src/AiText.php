<?php

namespace Lenorix\Ai;

use Lenorix\Ai\Chat\CoreChatCompletionResponse;
use Lenorix\Ai\Provider\ChatCompletion;

class AiText
{
    public static function generate(
        ChatCompletion $provider,
        array $tools = [],
        array $messages = [],
        ?string $prompt = null,
        ?string $system = null,
        ?float $temperature = null,
        ?int $maxSteps = null,
        ?string $toolChoice = null
    ): CoreChatCompletionResponse {
        return $provider->generate(
            $tools,
            $messages,
            $prompt,
            $system,
            $temperature,
            $maxSteps,
            $toolChoice
        );
    }
}
