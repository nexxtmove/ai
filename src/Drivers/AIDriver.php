<?php

namespace Nexxtmove\AI\Drivers;

use Nexxtmove\AI\Tool;

abstract class AIDriver
{
    /**
     * @param string $prompt
     * @param array{model: string, tools: Tool[]} $options
     */
    abstract public function ask(string $prompt, array $options = []): string;

    abstract protected function formatTool(Tool $tool): array;

    abstract protected function callTool(array $availableTools, string $toolName, array $parameters): string;
}
