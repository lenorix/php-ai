<?php

namespace Lenorix\Ai\Chat;

class CoreMessage
{
    public CoreMessageRole $role;

    public mixed $content;

    public function __construct(
        CoreMessageRole $role,
        mixed $content = null,
    ) {
        $this->role = $role;
        $this->content = $content;
    }

    public static function fromArray(array $message): self
    {
        return new self(
            role: CoreMessageRole::from($message['role']),
            content: $message['content'] ?? null,
        );
    }
}
