<?php

namespace Lenorix\Ai\Chat;

class CoreMessage
{
    public CoreMessageRole $role;

    public mixed $content;

    public ?array $toolCalls = null;

    public function __construct(
        CoreMessageRole $role,
        mixed $content = null,
        ?array $toolCalls = null,
    ) {
        $this->role = $role;
        $this->content = $content;

        if ($toolCalls) {
            $this->toolCalls = $toolCalls;
        }
    }

    public static function fromArray(array $message): self
    {
        return new self(
            role: CoreMessageRole::from($message['role']),
            content: $message['content'] ?? null,
            toolCalls: $message['tool_calls'] ?? null,
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

        return $message;
    }
}
