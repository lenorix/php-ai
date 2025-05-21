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
        ?string $system = null,
        ?int $maxSteps = null
    ): CoreChatCompletionResponse {
        return $provider->generate($tools, $messages, $system, $maxSteps);
    }
}
