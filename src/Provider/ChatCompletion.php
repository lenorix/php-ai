<?php

namespace Lenorix\Ai\Provider;

use Lenorix\Ai\Chat\CoreChatCompletionResponse;
use Lenorix\Ai\Provider;

/**
 * Interface for providers that support chat completion
 * with tools.
 */
interface ChatCompletion extends Provider
{
    public function generate(
        array $tools = [],
        array $messages = [],
        ?string $prompt = null,
        ?string $system = null,
        ?float $temperature = null,
        ?int $maxSteps = null,
        ?string $toolChoice = null
    ): CoreChatCompletionResponse;
}
