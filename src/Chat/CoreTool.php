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

    public function requiredParameters(): array
    {
        return array_keys((array) $this->parameters());
    }

    abstract public function run(...$arguments): mixed;

    public function returnCaughtException(\Exception $exception): mixed
    {
        return [
            'error' => [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ],
        ];
    }

    public function parametersSchema(): \stdClass
    {
        $schema = new \stdClass();
        $schema->type = 'object';
        $schema->properties = $this->parameters();
        $schema->required = $this->requiredParameters();
        return $schema;
    }

    public function toArray(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => $this->description(),
                'parameters' => $this->parametersSchema(),
            ],
        ];
    }
}
