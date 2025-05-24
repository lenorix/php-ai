<?php

use Lenorix\Ai\Chat\CoreMessage;
use Lenorix\Ai\Chat\CoreMessageRole;
use Lenorix\Ai\Chat\CoreTool;

it('check JSON serialization of CoreMessageRole', function () {
    expect(json_encode(CoreMessageRole::USER))->toBe('"user"')
        ->and(json_encode(CoreMessageRole::ASSISTANT))->toBe('"assistant"')
        ->and(json_encode(CoreMessageRole::SYSTEM))->toBe('"system"')
        ->and(json_encode(CoreMessageRole::TOOL))->toBe('"tool"');
});

it('check JSON serialization of CoreMessage of USER role', function () {
    expect(json_encode(new CoreMessage(
        role: CoreMessageRole::USER,
        content: 'Hello, world!'
    )))
        ->toContain(
            '"role":"user"',
            '"content":"Hello, world!"'
        );
});

it('check JSON serialization of CoreMessage of ASSISTANT role with a reply', function () {
    expect(json_encode(new CoreMessage(
        role: CoreMessageRole::ASSISTANT,
        content: 'Hello, user!'
    )))
        ->toContain(
            '"role":"assistant"',
            '"content":"Hello, user!"'
        );
});

it('check JSON serialization of CoreMessage of ASSISTANT role with a tool calls', function () {
    expect(json_encode(new CoreMessage(
        role: CoreMessageRole::ASSISTANT,
        toolCalls: [
            [
                'id' => 'tool-call-1',
                'type' => 'function',
                'function' => [
                    'name' => 'exampleFunction',
                    'arguments' => [
                        'arg1' => 'value1',
                        'arg2' => 'value2',
                    ],
                ],
            ],
        ]
    )))
        ->toContain(
            '"role":"assistant"',
            '"tool_calls":[{"id":"tool-call-1","type":"function","function":{"name":"exampleFunction","arguments":{"arg1":"value1","arg2":"value2"}}}]'
        );
});

it('check JSON serialization of CoreMessage of ASSISTANT role with a tool calls and empty reply', function () {
    expect(json_encode(new CoreMessage(
        role: CoreMessageRole::ASSISTANT,
        content: '',
        toolCalls: [
            [
                'id' => 'tool-call-1',
                'type' => 'function',
                'function' => [
                    'name' => 'exampleFunction',
                    'arguments' => [
                        'arg1' => 'value1',
                        'arg2' => 'value2',
                    ],
                ],
            ],
        ]
    )))
        ->toContain(
            '"role":"assistant"',
            '"content":""',
            '"tool_calls":[{"id":"tool-call-1","type":"function","function":{"name":"exampleFunction","arguments":{"arg1":"value1","arg2":"value2"}}}]'
        );
});

it('check JSON serialization of CoreMessage with tool call ID', function () {
    expect(json_encode(new CoreMessage(
        role: CoreMessageRole::TOOL,
        content: '{"property1":"Tool call result"}',
        toolCallId: 'tool-call-id-123'
    )))
        ->toContain(
            '"role":"tool"',
            '"content":"{\"property1\":\"Tool call result\"}"',
            '"tool_call_id":"tool-call-id-123"'
        );
});

it('check JSON serialization of CoreTool', function () {
    $tool = new class() extends CoreTool {
        public function description(): string
        {
            return 'Test tool';
        }

        public function run(...$arguments): mixed
        {
            return null;
        }
    };

    expect(json_encode($tool))
        ->toContain(
            '"name":' . json_encode($tool->name()),
            '"description":"Test tool"',
            '"properties":{}',
            '"required":[]',
            '"type":"function"'
        );
});
