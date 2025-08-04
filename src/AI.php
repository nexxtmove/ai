<?php

namespace Nexxtmove;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;

class AI
{
    private string $question;

    private Provider|string|null $provider;

    private ?string $model;

    public static function ask(string $question)
    {
        $self = new self();

        $self->question = $question;

        $self->provider = config('ai.default_provider');
        $self->model = config('ai.default_model');

        return $self;
    }

    public function get()
    {
        if (!$this->provider) {
            throw new Exception('Provider is not set.');
        }

        if (!$this->model) {
            throw new Exception('Model is not set.');
        }

        $response = Prism::text()
            ->using($this->provider, $this->model)
            ->withPrompt($this->question)
            ->asText();

        return $response->text;
    }
}
