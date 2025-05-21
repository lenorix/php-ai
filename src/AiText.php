<?php

namespace Lenorix\Ai;

use Lenorix\Ai\Chat\CoreChatResponse;
use Lenorix\Ai\Chat\CoreMessage;
use Lenorix\Ai\Provider\ChatCompletion;

class AiText
{
    public static function generate(
        ChatCompletion $provider,
        array $tools = [],
        array $messages = [],
        ?string $system = null,
    ): CoreChatResponse {
        return $provider->generate($tools, $messages, $system);
    }
}
