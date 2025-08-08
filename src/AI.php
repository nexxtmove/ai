<?php

namespace Nexxtmove;

use Exception;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class AI
{
    private string $question;

    private Provider|string|null $provider = null;

    private ?string $model = null;

    private ?string $outputClass = null;

    public static function ask(string $question)
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

    public function get()
    {
        if (! $this->provider) {
            throw new Exception('Provider is not set.');
        }

        if (! $this->model) {
            throw new Exception('Model is not set.');
        }

        $response = $this->outputClass ? Prism::structured() : Prism::text();

        $response = $response
            ->using($this->provider, $this->model)
            ->withPrompt($this->question);

        if ($this->outputClass) {
            $schema = OutputSchemaBuilder::build($this->outputClass);

            $response = $response
                ->withSchema($schema)
                ->asStructured();

            return $response->structured;
        }

        return $response->asText()->text;
    }
}
