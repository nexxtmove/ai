<?php

namespace Nexxtmove;

use Exception;
use Prism\Prism\Contracts\Schema;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use ReflectionClass;
use ReflectionProperty;

class AI
{
    private string $question;

    private Provider|string|null $provider = null;

    private ?string $model = null;

    private ?Schema $rawSchema = null;

    /** @var callable[] */
    private array $functions = [];

    private ?string $outputClass = null;

    private ?string $systemPrompt = null;

    public static function ask(string $question): self
    {
        $self = new self;

        $self->question = $question;

        $self->provider = config('ai.default_provider');
        $self->model = config('ai.default_model');

        return $self;
    }

    public function using(Provider $provider, string $model): self
    {
        $this->provider = $provider;
        $this->model = $model;

        return $this;
    }

    public function output(string $class): self
    {
        $this->outputClass = $class;

        return $this;
    }

    public function withSystemPrompt(string $prompt): self
    {
        $this->systemPrompt = $prompt;

        return $this;
    }

    public function functions(array $functions): self
    {
        $this->functions = $functions;

        return $this;
    }

    public function rawSchema(array $schema): self
    {
        $rawSchema = new class implements Schema
        {
            public array $schema;

            public function name(): string
            {
                return 'raw_schema';
            }

            public function toArray(): array
            {
                return $this->schema;
            }
        };

        $rawSchema->schema = $schema;
        $this->rawSchema = $rawSchema;

        return $this;
    }

    public function get()
    {
        if (! $this->provider) {
            throw new Exception('Provider is not set.');
        }

        if (! $this->model) {
            throw new Exception('Model is not set.');
        }

        $response = ($this->outputClass || $this->rawSchema) ? Prism::structured() : Prism::text();

        $response = $response
            ->using($this->provider, $this->model)
            ->withPrompt($this->question);

        if ($this->systemPrompt) {
            $response = $response->withSystemPrompt($this->systemPrompt);
        }

        if ($this->functions) {
            $tools = [];
            foreach ($this->functions as $function) {
                $tools[] = FunctionToolBuilder::build($function);
            }
            $response = $response->withTools($tools);
        }

        if ($this->outputClass) {
            $schema = OutputSchemaBuilder::build($this->outputClass);

            $response = $response
                ->withSchema($schema)
                ->asStructured();

            return $this->mapArrayToClass($response->structured, $this->outputClass);
        }

        if ($this->rawSchema) {
            $response = $response
                ->withSchema($this->rawSchema)
                ->asStructured();

            return $response->structured;
        }

        return $response->asText()->text;
    }

    private function mapArrayToClass(array $data, string $class): object
    {
        $reflectionClass = new ReflectionClass($class);
        $instance = $reflectionClass->newInstanceWithoutConstructor();
        $properties = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            if (! array_key_exists($propertyName, $data)) {
                continue;
            }

            $value = $data[$propertyName];
            $type = $property->getType();

            if ($type && $type->getName() === 'array') {
                // Handle arrays - check if it's an array of objects
                $arrayItemType = $this->getArrayItemTypeFromDocComment($property);
                if ($arrayItemType && class_exists($arrayItemType) && is_array($value)) {
                    // Map each array element to the specified class
                    $value = array_map(function ($item) use ($arrayItemType) {
                        return is_array($item) ? $this->mapArrayToClass($item, $arrayItemType) : $item;
                    }, $value);
                }
            } elseif ($type && ! $type->isBuiltin() && class_exists($type->getName())) {
                // Handle nested objects
                if (is_array($value)) {
                    $value = $this->mapArrayToClass($value, $type->getName());
                }
            }

            $property->setValue($instance, $value);
        }

        return $instance;
    }

    /**
     * Extract array item type from PHPDoc comment.
     * For example: "Weather[]" -> "Weather", "string[]" -> "string"
     */
    private function getArrayItemTypeFromDocComment(ReflectionProperty $property): ?string
    {
        $docComment = $property->getDocComment();
        if (! $docComment) {
            return null;
        }

        // Match @var type annotations like Weather[] or string[]
        if (preg_match('/@var\s+([^\s\[\]]+)\[\]/', $docComment, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
