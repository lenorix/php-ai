<?php

namespace Lenorix\Ai\Chat;

use Swaggest\JsonSchema\Schema;
use Swaggest\JsonSchema\SchemaContract;

abstract class CoreTool implements \JsonSerializable
{
    public function name(): string
    {
        $name = str_replace("\0", '', static::class);
        $name = str_replace('\\', '/', $name);
        $name = str_replace(':', '?l=', $name);
        $name = str_replace('$', '&c=', $name);
        return $name;
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

    protected function parametersSchema(): \stdClass
    {
        $schema = new \stdClass();

        $schema->type = 'object';
        $schema->properties = $this->parameters();
        $schema->required = $this->requiredParameters();

        return $schema;
    }

    /**
     * Returns the JSON schema for the parameters validation.
     *
     * @return SchemaContract
     * @throws \Swaggest\JsonSchema\Exception If the schema does not conform to the JSON Schema specification.
     */
    public function parametersSchemaContract(): SchemaContract
    {
        // TODO: Move this as early as possible to the constructor
        // to fail fast if the schema is invalid, making issues
        // easier to be found.
        return Schema::import($this->parametersSchema());
    }

    /**
     * Converts the tool to an array representation.
     *
     * @return array The array representation of the tool.
     * @throws \Swaggest\JsonSchema\Exception If the schema does not conform to the JSON Schema specification.
     */
    public function toArray(): array
    {
        Schema::schema()->in($this->parametersSchema());
        return [
            'type' => 'function',
            'function' => [
                'name' => $this->name(),
                'description' => $this->description(),
                'parameters' => $this->parametersSchema(),
            ],
        ];
    }

    /**
     * Converts the tool to a JSON-serializable object.
     *
     * @return mixed The JSON-serializable representation of the tool.
     * @throws \Swaggest\JsonSchema\Exception If the schema does not conform to the JSON Schema specification.
     */
    public function jsonSerialize(): mixed
    {
        return (object) $this->toArray();
    }
}
