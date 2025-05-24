<?php

namespace Lenorix\Ai\Chat;

class CoreMessage implements \JsonSerializable
{
    public CoreMessageRole $role;

    public mixed $content;

    public ?array $toolCalls = null;

    public ?string $toolCallId = null;

    public function __construct(
        CoreMessageRole $role,
        mixed $content = null,
        ?array $toolCalls = null,
        ?string $toolCallId = null,
    ) {
        $this->role = $role;
        $this->content = $content;

        if ($toolCalls) {
            $this->toolCalls = $toolCalls;
        }

        if ($toolCallId) {
            $this->toolCallId = $toolCallId;
        }
    }

    public static function fromArray(array $message): self
    {
        return new self(
            role: $message['role'] instanceof CoreMessageRole
                ? $message['role']
                : CoreMessageRole::from($message['role']),
            content: $message['content'] ?? null,
            toolCalls: $message['tool_calls'] ?? null,
            toolCallId: $message['tool_call_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        $message = [
            'role' => $this->role->value,
            'content' => $this->content,
        ];

        if ($this->toolCalls) {
            $message['tool_calls'] = $this->toolCalls;
        }

        if ($this->toolCallId) {
            $message['tool_call_id'] = $this->toolCallId;
        }

        return $message;
    }

    public function jsonSerialize(): mixed
    {
        return (object) $this->toArray();
    }
}
