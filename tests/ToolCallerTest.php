<?php

use Lenorix\Ai\Chat\CoreMessage;
use Lenorix\Ai\Chat\CoreMessageRole;
use Lenorix\Ai\Tool\ToolCaller;

it('handle tool missed', function () {
    $toolCaller = new ToolCaller([]);
    $messages = $toolCaller->callTools([
        [
            'id' => 'tool-test-handle-missed',
            'type' => 'function',
            'function' => [
                'name' => 'testFunction',
                'arguments' => [
                    'param1' => 'value1',
                    'param2' => 'value2',
                ],
            ],
        ],
    ]);

    expect($messages)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($messages[0])
        ->toBeInstanceOf(CoreMessage::class)
        ->toHaveProperty('toolCallId', 'tool-test-handle-missed')
        ->toHaveProperty('role', CoreMessageRole::TOOL)
        ->toHaveProperty('content')
        ->and($messages[0]->content)
        ->toBeString()
        ->toContain('"error":{"');
});

it('handle tool call', function () {
    $tool = new class extends \Lenorix\Ai\Chat\CoreTool
    {
        public function description(): string
        {
            return 'Test tool';
        }

        public function run(...$arguments): mixed
        {
            return [
                'result' => 'success',
                'arguments' => $arguments,
            ];
        }
    };

    $toolCaller = new ToolCaller([$tool]);
    $messages = $toolCaller->callTools([
        [
            'id' => 'tool-test-handle-call',
            'type' => 'function',
            'function' => [
                'name' => $tool->name(),
                'arguments' => [
                    'param1' => 'value1',
                    'param2' => 'value2',
                ],
            ],
        ],
    ]);

    expect($messages)
        ->toBeArray()
        ->toHaveCount(1)
        ->and($messages[0])
        ->toBeInstanceOf(CoreMessage::class)
        ->toHaveProperty('toolCallId', 'tool-test-handle-call')
        ->toHaveProperty('role', CoreMessageRole::TOOL)
        ->toHaveProperty('content')
        ->and($messages[0]->content)
        ->toBeString()
        ->toContain('"result":"success"');
});
