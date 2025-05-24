<?php

namespace Lenorix\Ai\Tool;

use Lenorix\Ai\Chat\CoreMessage;
use Lenorix\Ai\Chat\CoreMessageRole;
use Lenorix\Ai\Chat\CoreTool;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Swaggest\JsonSchema\Schema;


class ToolCaller
{
    /** @var CoreTool[] */
    protected readonly array $toolsByName;
    protected readonly LoggerInterface $logger;

    public function __construct(
        /** @var CoreTool[] $tools */
        array $tools,
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();

        $toolsByName = [];
        foreach ($tools as $tool) {
            if (! $tool instanceof CoreTool) {
                $this->logger->error('Tool is not an instance of CoreTool.', ['tool' => $tool]);
                throw new \Exception('All tools must be instances of CoreTool.');
            }
            $toolsByName[$tool->name()] = $tool;
        }
        $this->toolsByName = $toolsByName;
    }

    /**
     * Executes the provided tool calls.
     *
     * @param array $toolCalls An array of tool calls, each containing the tool name and parameters.
     * @return CoreMessage[] An array of results from executing the tool calls, with tool role each message.
     * @throws \Exception If a tool call does not have at least a valid ID.
     */
    public function callTools(array $toolCalls): array
    {
        $results = [];

        foreach ($toolCalls as $toolCall) {
            // Only really mandatory to handle the tools interaction is an ID,
            // without it any try to complete the chat will be rejected (at least by DeepSeek).
            $callId = $this->getIdForToolCall($toolCall);

            // The rest of errors could be handled returning the error to the LLM.
            $toolMessage = new CoreMessage(
                role: CoreMessageRole::TOOL,
                content: json_encode($this->toolCall($toolCall)),
                toolCallId: $callId,
            );
            $results[] = $toolMessage;
        }

        return $results;
    }

    protected function getIdForToolCall(array $toolCall): string
    {
        $callId = $toolCall['id'] ?? null;

        if (! is_string($callId)) {
            $this->logger->error('The "id" key must be a string.', ['toolCall' => $toolCall]);
            throw new \Exception('The "id" key must be a string.');
        }

        return $callId;
    }

    protected function toolCall(array $toolCall): mixed
    {
        try {
            $tool = $this->findToolForToolCall($toolCall);
        } catch (\Exception $e) {
            $this->logger->error('Invalid tool call.', [
                'toolCall' => $toolCall,
                'error' => $e,
            ]);
            return [
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                ],
            ];
        }

        try {
            $arguments = $this->argumentsForToolCall($toolCall);
        } catch (\Exception $e) {
            $this->logger->error('Invalid tool call without arguments.', [
                'toolCall' => $toolCall,
                'error' => $e,
            ]);
            return $tool->returnCaughtException($e);
        }

        try {
            $schema = Schema::import($tool->parametersSchema());
            // NOTE: No, no move the (object) to $arguments = (object)... because then
            // are not associative array to use ... operator for tool->run(...$arguments)
            // but here is required to validate the arguments JSON schema correctly.
            $schema->in((object)$arguments);
        } catch (\Exception $e) {
            $this->logger->error('Invalid arguments for tool call.', [
                'toolCall' => $toolCall,
                'error' => $e,
            ]);
            return $tool->returnCaughtException($e);
        }

        try {
            return $tool->run(...$arguments);
        } catch (\Exception $e) {
            $this->logger->error('Error executing tool call.', [
                'toolCall' => $toolCall,
                'error' => $e,
            ]);
            return $tool->returnCaughtException($e);
        }
    }

    /**
     * Finds the tool for the given tool call.
     *
     * @param array $toolCall The tool call to find the tool for.
     * @return CoreTool The found tool.
     * @throws \Exception If the tool call is invalid or the tool is not found.
     */
    protected function findToolForToolCall(array $toolCall): CoreTool
    {
        $function = $toolCall['function'] ?? [];
        $name = $function['name'] ?? null;

        if (! is_string($name)) {
            $this->logger->error('The "function.name" key must be a string.', ['toolCall' => $toolCall]);
            throw new \Exception('The "function.name" key must be a string.');
        }
        if (! array_key_exists($name, $this->toolsByName)) {
            $this->logger->error("Tool with name '$name' not found.", ['toolCall' => $toolCall]);
            throw new \Exception("Tool with name '$name' not found.");
        }

        return $this->toolsByName[$name];
    }

    protected function argumentsForToolCall(array $toolCall): array
    {
        $function = $toolCall['function'] ?? [];
        $arguments = $function['arguments'] ?? null;

        if(is_null($arguments)) {
            $this->logger->error('Tool call must have "function.arguments" key value.', ['toolCall' => $toolCall]);
            throw new \Exception('A tool calls item must contain a "function.arguments" key value.');
        }

        return $arguments;
    }
}
