<?php

namespace Nexxtmove\AI;

interface Tool {
    public function name(): string;

    public function description(): string;

    /**
     * @return array JSON Schema for the parameters expected by the tool.
     */
    public function parameters(): array;

    public function call(array $parameters): string;
}
