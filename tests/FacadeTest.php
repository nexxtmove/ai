<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Nexxtmove\AI;

beforeEach(function () {
    Http::fake();
});

test('Facade works', function () {
    $response = AI::ask('How are you?');

    expect($response)->toBeNull();
});

test('Facade uses the correct driver', function () {
    Config::set('ai.driver', 'gemini');

    AI::ask('How are you?');

    Http::assertSent(function (Request $request) {
        return str_contains($request->url(), 'google');
    });
});
