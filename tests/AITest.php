<?php

use Illuminate\Support\Facades\Config;
use Nexxtmove\AI;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Prism;
use Prism\Prism\Testing\TextResponseFake;

beforeEach(function () {
    // Default fake response for Prism
    Prism::fake([TextResponseFake::make()]);

    // Default provider/model
    Config::set('ai.default_provider', Provider::OpenAI);
    Config::set('ai.default_model', 'gpt-4o');
});

test('require provider', function () {
    Config::set('ai.default_provider', null);
    AI::ask('...')->get();
})->throws(Exception::class, 'Provider is not set.');

test('require model', function () {
    Config::set('ai.default_model', null);
    AI::ask('...')->get();
})->throws(Exception::class, 'Model is not set.');

test('load default provider and model', function () {
    AI::ask('...')->get();
})->throwsNoExceptions();

test('simple question', function () {
    $fakeResponse = TextResponseFake::make()->withText('2');
    Prism::fake([$fakeResponse]);

    $result = AI::ask('What is 1+1?')->get();

    expect($result)->toBe('2');
});
