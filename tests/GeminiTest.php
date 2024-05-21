<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Nexxtmove\Drivers\Gemini;

test('ask calls Gemini API', function () {
    Http::fake();

    $gemini = new Gemini();

    $gemini->ask('How are you?');

    Http::assertSent(function (Request $request) {
        return str_starts_with($request->url(), 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent');
    });
});

test('sends question', function () {
    Http::fake();

    $gemini = new Gemini();

    $gemini->ask('How are you?');

    Http::assertSent(function (Request $request) {
        return $request['contents'][0]['parts'][0]['text'] === 'How are you?';
    });
});

test('uses Gemini key', function () {
    Http::fake();

    Config::set('ai.gemini.api_key', 'abc');

    $gemini = new Gemini();

    $gemini->ask('How are you?');

    Http::assertSent(function (Request $request) {
        return str_ends_with($request->url(), 'key=abc');
    });
});

test('uses correct model', function () {
    Http::fake();

    Config::set('ai.model', 'gemini-pro-vision');

    $gemini = new Gemini();

    $gemini->ask('How are you?');

    Http::assertSent(function (Request $request) {
        return str_starts_with($request->url(), 'https://generativelanguage.googleapis.com/v1/models/gemini-pro-vision:generateContent');
    });
});

test('returns response from api', function () {
    Http::fake([
        'generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent*' => Http::response([
            'candidates' => [
                [
                    'content' => [
                        'role' => 'model',
                        'parts' => [
                            [
                                'text' => 'I am fine, thank you!',
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $gemini = new Gemini();

    $response = $gemini->ask('How are you?');

    expect($response)->toBe('I am fine, thank you!');
});

test('throws exception if api call fails', function () {
    Http::fake([
        'generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent*' => Http::response([
            'error' => [
                'code' => 400,
                'message' => 'API key expired. Please renew the API key.',
            ],
        ], 400),
    ]);

    $gemini = new Gemini();

    $gemini->ask('How are you?');
})->throws('API key expired.');
