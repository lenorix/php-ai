<?php

namespace Lenorix\Ai\Provider;

use Lenorix\Ai\Chat\CoreChatCompletionResponse;
use Lenorix\Ai\Provider;

interface ChatCompletion extends Provider
{
    public function generate(
        array $tools = [],
        array $messages = [],
        ?string $system = null,
    ): CoreChatCompletionResponse;
}
