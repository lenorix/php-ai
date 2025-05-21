<?php

namespace Lenorix\Ai\Chat;

abstract class CoreTool
{
    public function name(): string
    {
        return static::class;
    }

    abstract public function description(): string;

    public function parameters(): array
    {
        return [];
    }

    abstract public function execute(...$parameters): mixed;
}
