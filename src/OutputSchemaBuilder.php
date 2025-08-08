<?php

namespace Nexxtmove;

use Prism\Prism\Contracts\Schema;
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
    public static function build(string $class): ObjectSchema
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
            name: $reflected->getShortName(),
            description: 'Structured output for '.$reflected->getName(),
            properties: $propertySchemas,
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

        switch ($type->getName()) {
            case 'int':
            case 'float':
                return new NumberSchema($name, '');

            case 'string':
                return new StringSchema($name, '');

            case 'bool':
            case 'boolean':
                return new BooleanSchema($name, '');

            default:
                return null; // Unsupported type
        }
    }
}
