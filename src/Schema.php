<?php

namespace Nexxtmove\AI;

class Schema
{
    public static function string(array $properties = []): array
    {
        return ['type' => 'string', ...$properties];
    }

    public static function number(array $properties = []): array
    {
        return ['type' => 'number', ...$properties];
    }

    public static function integer(array $properties = []): array
    {
        return ['type' => 'integer', ...$properties];
    }

    public static function boolean(array $properties = []): array
    {
        return ['type' => 'boolean', ...$properties];
    }

    public static function object(array $properties, array $options = []): array
    {
        $schema = [
            'type' => 'object',
            'properties' => $properties,
            'required' => array_keys($properties),
            'additionalProperties' => false,
        ];

        return [...$schema, ...$options];
    }

    public static function array($items, array $properties = []): array
    {
        $schema = [
            'type' => 'array',
            'items' => $items,
        ];

        return [...$schema, ...$properties];
    }

    public static function ref(string $name): array
    {
        return ['$ref' => "#/\$defs/{$name}"];
    }

    public static function anyOf(array $schemas): array
    {
        return ['anyOf' => $schemas];
    }

    public static function nullable($schema): array
    {
        if (isset($schema['anyOf'])) {
            return ['anyOf' => [...$schema['anyOf'], ['type' => 'null']]];
        }

        return ['anyOf' => [$schema, ['type' => 'null']]];
    }

    public static function enum(array $values, array $properties = []): array
    {
        $schema = ['enum' => $values];
        return [...$schema, ...$properties];
    }
}
