<?php

namespace Lenorix\Ai\Chat;

abstract class CoreTool
{
    public function name(): string
    {
        return static::class;
    }

    abstract public function description(): string;

    abstract public function parameters(): array;

    abstract public function execute(...$parameters): mixed;
}
