<?php

namespace Lenorix\Ai\Chat;

/**
 * Use `CoreTool` to create a tool, but this can
 * conveniently convert a lambda function to a tool.
 *
 * @property callable $lambda
 * @property string $name
 * @property string $description
 * @property array $parameters
 *
 * @throws \InvalidArgumentException
 */
class ToolFromLambda extends CoreTool
{
    public function __construct(
        public $lambda,
        public string $name,
        public string $description,
        public array $parameters = [],
        public ?array $requiredParameters = null,
    ) {
        if (! is_callable($this->lambda)) {
            throw new \InvalidArgumentException('lambda must be callable');
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function parameters(): object
    {
        return (object) $this->parameters;
    }

    public function requiredParameters(): array
    {
        return $this->requiredParameters ?: array_keys((array) $this->parameters());
    }

    public function execute(...$parameters): mixed
    {
        return ($this->lambda)(...$parameters);
    }
}
