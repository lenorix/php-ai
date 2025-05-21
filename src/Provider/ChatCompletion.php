<?php

namespace Lenorix\Ai\Provider;

use Lenorix\Ai\Chat\CoreChatCompletionResponse;
use Lenorix\Ai\Provider;

interface ChatCompletion extends Provider
{
    public function generate(
        array $tools = [],
        array $messages = [],
        ?string $prompt = null,
        ?string $system = null,
        ?int $maxSteps = null
    ): CoreChatCompletionResponse;
}
