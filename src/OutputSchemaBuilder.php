<?php

namespace Nexxtmove;

use Prism\Prism\Contracts\Schema;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use ReflectionClass;
use ReflectionProperty;

/**
 * Builds a Prism ObjectSchema from a given PHP class using reflection.
 */
class OutputSchemaBuilder
{
    /**
     * Build an ObjectSchema based on the public properties of the provided class.
     */
    public static function build(string $class, ?string $name = null): ObjectSchema
    {
        $reflected = new ReflectionClass($class);
        $properties = $reflected->getProperties(ReflectionProperty::IS_PUBLIC);

        $propertySchemas = [];
        foreach ($properties as $property) {
            $schema = self::schemaFromProperty($property);
            if ($schema) {
                $propertySchemas[] = $schema;
            }
        }

        return new ObjectSchema(
            name: $name ?? $reflected->getShortName(),
            description: 'Structured output for '.$reflected->getName(),
            properties: $propertySchemas,
            requiredFields: array_map(fn ($schema) => $schema->name, $propertySchemas)
        );
    }

    /**
     * Map a reflected property to a Prism Schema instance (if supported).
     */
    private static function schemaFromProperty(ReflectionProperty $property): ?Schema
    {
        $type = $property->getType();
        if (! $type) {
            return null; // Skip untyped properties
        }

        $name = $property->getName();
        $typeName = $type->getName();

        if ($typeName === 'array') {
            $itemType = self::getArrayItemTypeFromDocComment($property);
            $items = $itemType ? self::schemaForType($itemType, 'item') : null;

            return new ArraySchema(
                $name,
                description: '',
                items: $items
            );
        }

        return self::schemaForType($typeName, $name);
    }

    /**
     * Extract array item type from PHPDoc comment.
     * For example: "string[]" -> "string", "int[]" -> "int"
     */
    private static function getArrayItemTypeFromDocComment(ReflectionProperty $property): ?string
    {
        $docComment = $property->getDocComment();
        if (! $docComment) {
            return null;
        }

        // Match @var type annotations
        if (preg_match('/@var\s+([^\s\[\]]+)\[\]/', $docComment, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Create a schema for a given type name.
     */
    private static function schemaForType(string $type, string $name): ?Schema
    {
        switch ($type) {
            case 'int':
            case 'integer':
            case 'float':
            case 'double':
                return new NumberSchema($name, description: '');

            case 'string':
                return new StringSchema($name, description: '');

            case 'bool':
            case 'boolean':
                return new BooleanSchema($name, description: '');
        }

        // Check if it's a custom class that we can build a schema for
        if (class_exists($type)) {
            return self::build($type, $name);
        }

        return null; // Unsupported type
    }
}
