<?php

namespace Lenorix\Ai\Chat;

enum CoreMessageRole: string implements \JsonSerializable
{
    case SYSTEM = 'system';
    case USER = 'user';
    case ASSISTANT = 'assistant';
    case TOOL = 'tool';

    public function jsonSerialize(): mixed
    {
        return $this->value;
    }
}
