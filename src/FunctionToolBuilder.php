<?php

namespace Nexxtmove;

use Closure;
use Prism\Prism\Facades\Tool;
use Prism\Prism\Tool as PrismTool;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Builds a Prism Tool from a given PHP callable using reflection.
 */
class FunctionToolBuilder
{
    /**
     * Build a Tool based on the provided callable function.
     */
    public static function build(callable $function): PrismTool
    {
        $reflection = self::getReflection($function);

        $tool = Tool::as(self::getName($function, $reflection))
            ->for(self::getDescription($reflection))
            ->using($function);

        foreach ($reflection->getParameters() as $parameter) {
            $name = $parameter->getName();
            $required = ! $parameter->isOptional();
            $typeName = $parameter->getType()?->getName();

            match ($typeName) {
                'string' => $tool->withStringParameter($name, "Parameter {$name}", $required),
                'int', 'float' => $tool->withNumberParameter($name, "Parameter {$name}", $required),
                'bool' => $tool->withBooleanParameter($name, "Parameter {$name}", $required),
                'array' => $tool->withArrayParameter($name, "Parameter {$name}", new \Prism\Prism\Schema\StringSchema('item', ''), $required),
                default => $tool->withStringParameter($name, "Parameter {$name}", $required),
            };
        }

        return $tool;
    }

    private static function getReflection(callable $function): ReflectionFunction|ReflectionMethod
    {
        return match (true) {
            is_array($function) => new ReflectionMethod($function[0], $function[1]),
            is_object($function) && method_exists($function, '__invoke') && ! $function instanceof Closure => new ReflectionMethod($function, '__invoke'),
            default => new ReflectionFunction($function),
        };
    }

    private static function getName(callable $function, ReflectionFunction|ReflectionMethod $reflection): string
    {
        return match (true) {
            is_string($function) => $function,
            is_array($function) => $function[1] ?? '__invoke',
            default => $reflection->getName(),
        };
    }

    private static function getDescription(ReflectionFunction|ReflectionMethod $reflection): string
    {
        $docComment = $reflection->getDocComment();

        if (! $docComment) {
            return $reflection->getName();
        }

        // Extract first meaningful line from docblock
        $lines = array_map(fn ($line) => ltrim(trim($line), '* '), explode("\n", $docComment));

        foreach ($lines as $line) {
            if ($line && $line !== '/**' && $line !== '*/' && ! str_starts_with($line, '@')) {
                return $line;
            }
        }

        return $reflection->getName();
    }
}
