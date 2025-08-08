<?php

use Illuminate\Support\Facades\Config;
use Nexxtmove\AI;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Testing\StructuredResponseFake;
use Prism\Prism\Testing\TextResponseFake;

beforeEach(function () {
    Config::set('ai.default_provider', 'openai');
    Config::set('ai.default_model', 'gpt-4o');
});

it('requires a provider to be set', function () {
    Config::set('ai.default_provider', null);
    AI::ask('...')->get();
})->throws(Exception::class, 'Provider is not set.');

it('requires a model to be set', function () {
    Config::set('ai.default_model', null);
    AI::ask('...')->get();
})->throws(Exception::class, 'Model is not set.');

it('loads the default provider and model from config', function () {
    $fake = Prism::fake([TextResponseFake::make()]);

    AI::ask('...')->get();

    $fake->assertRequest(function ($requests) {
        expect($requests[0]->provider())->toBe('openai');
        expect($requests[0]->model())->toBe('gpt-4o');
    });
});

it('allows to use a specific provider and model', function () {
    $fake = Prism::fake([TextResponseFake::make()]);

    AI::ask('...')
        ->using(Provider::Gemini, 'gemini-2.0-flash')
        ->get();

    $fake->assertRequest(function ($requests) {
        expect($requests[0]->provider())->toBe('gemini');
        expect($requests[0]->model())->toBe('gemini-2.0-flash');
    });
});

it('can answer a question', function () {
    $fake = Prism::fake([
        TextResponseFake::make()->withText('2'),
    ]);

    $result = AI::ask('What is 1+1?')->get();

    expect($result)->toBe('2');

    $fake->assertPrompt('What is 1+1?');
});

describe('structured output', function () {
    function itHandles(string $type, string $outputClass, string $testProperty)
    {
        it("handles `{$type}` properties", function () use ($outputClass, $testProperty, $type) {
            $fake = Prism::fake([StructuredResponseFake::make()]);

            AI::ask('...')
                ->output($outputClass)
                ->get();

            $fake->assertRequest(function ($requests) use ($testProperty, $type) {
                $schema = $requests[0]->schema()->toArray();
                $properties = $schema['properties'];

                expect($properties[$testProperty]['type'])->toBe($type);
            });
        });
    }

    class Report
    {
        public int $sales;
    }
    itHandles('number', Report::class, 'sales');

    class Article
    {
        public string $title;
    }
    itHandles('string', Article::class, 'title');

    class FeatureFlag
    {
        public bool $enabled;
    }
    itHandles('boolean', FeatureFlag::class, 'enabled');
});
