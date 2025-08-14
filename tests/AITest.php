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
    function itHandles(string $type, string $outputClass, string $testProperty, ?string $itemType = null)
    {
        it("handles `{$outputClass}`", function () use ($outputClass, $testProperty, $type, $itemType) {
            $fake = Prism::fake([StructuredResponseFake::make()]);

            AI::ask('...')
                ->output($outputClass)
                ->get();

            $fake->assertRequest(function ($requests) use ($testProperty, $type, $itemType) {
                $schema = $requests[0]->schema()->toArray();
                $properties = $schema['properties'];

                expect($properties[$testProperty]['type'])->toBe($type);

                if ($itemType) {
                    expect($properties[$testProperty]['items']['type'])->toBe($itemType);
                }
            });
        });
    }

    class TestNumber
    {
        public int $sales;
    }
    itHandles('number', TestNumber::class, 'sales');

    class TestString
    {
        public string $title;
    }
    itHandles('string', TestString::class, 'title');

    class TestBool
    {
        public bool $enabled;
    }
    itHandles('boolean', TestBool::class, 'enabled');

    class TestStringArray
    {
        /** @var string[] */
        public array $list;
    }
    itHandles('array', TestStringArray::class, 'list', 'string');

    class TestNumberArray
    {
        /** @var float[] */
        public array $list;
    }
    itHandles('array', TestNumberArray::class, 'list', 'number');

    it('handles nested classes', function () {
        class Child
        {
            public string $name;
        }

        class TestNested
        {
            public Child $child;
        }

        $fake = Prism::fake([StructuredResponseFake::make()]);

        AI::ask('...')
            ->output(TestNested::class)
            ->get();

        $fake->assertRequest(function ($requests) {
            $schema = $requests[0]->schema()->toArray();
            $properties = $schema['properties'];

            expect($properties['child']['type'])->toBe('object');
            expect($properties['child']['properties']['name']['type'])->toBe('string');
        });
    });

    it('handles deeply nested classes', function () {
        class Level2
        {
            public string $name;
        }

        class Level1
        {
            public Level2 $child;
        }

        class TestDeepNested
        {
            public Level1 $child;
        }

        $fake = Prism::fake([StructuredResponseFake::make()]);

        AI::ask('...')
            ->output(TestDeepNested::class)
            ->get();

        $fake->assertRequest(function ($requests) {
            $schema = $requests[0]->schema()->toArray();
            $properties = $schema['properties'];

            expect($properties['child']['type'])->toBe('object');
            expect($properties['child']['properties']['child']['properties']['name']['type'])->toBe('string');
        });
    });
});
