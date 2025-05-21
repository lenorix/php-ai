<?php

namespace Lenorix\Ai\Chat;

class CoreToolLambda extends CoreTool
{
    public function __construct(
        public $lambda,
        public string $name,
        public string $description,
        public array $parameters = [],
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

    public function execute(...$parameters): mixed
    {
        return ($this->lambda)(...$parameters);
    }
}
