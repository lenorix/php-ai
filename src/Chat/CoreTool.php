<?php

namespace Lenorix\Ai\Chat;

abstract class CoreTool
{
    public function name(): string
    {
        return static::class;
    }

    abstract public function description(): string;

    public function parameters(): object
    {
        return (object) [];
    }

    abstract public function execute(...$parameters): mixed;

    public function toArray(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => $this->description(),
                'parameters' => $this->parameters(),
            ],
        ];
    }
}
