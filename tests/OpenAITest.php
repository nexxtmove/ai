<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Nexxtmove\Drivers\OpenAI;

test('ask calls OpenAI API', function () {
    Http::fake();

    $openai = new OpenAI();

    $openai->ask('How are you?');

    Http::assertSent(function (Request $request) {
        return $request->url() === 'https://api.openai.com/v1/chat/completions'
            && $request['model'] === 'gpt-3.5-turbo';
    });
});

test('sends question', function () {
    Http::fake();

    $openai = new OpenAI();

    $openai->ask('How are you?');

    Http::assertSent(function (Request $request) {
        return $request['messages'][0]['content'] === 'How are you?';
    });
});

test('uses OpenAI key', function () {
    Http::fake();

    Config::set('ai.openai.api_key', 'sk-abc');

    $openai = new OpenAI();

    $openai->ask('How are you?');

    Http::assertSent(function (Request $request) {
        return $request->hasHeader('Authorization', 'Bearer sk-abc');
    });
});

test('uses correct model', function () {
    Http::fake();

    Config::set('ai.model', 'gpt-4o');

    $openai = new OpenAI();

    $openai->ask('How are you?');

    Http::assertSent(function (Request $request) {
        return $request['model'] === 'gpt-4o';
    });
});

test('returns response from api', function () {
    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'id' => 'chatcmpl-...',
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'I am fine, thank you!',
                    ],
                ],
            ],
        ]),
    ]);

    $openai = new OpenAI();

    $response = $openai->ask('How are you?');

    expect($response)->toBe('I am fine, thank you!');
});

test('throws exception if api call fails', function () {
    Http::fake([
        'api.openai.com/v1/chat/completions' => Http::response([
            'error' => [
                'code' => 400,
                'message' => 'API key expired. Please renew the API key.',
            ],
        ], 400),
    ]);

    $openai = new OpenAI();

    $openai->ask('How are you?');
})->throws('API key expired.');
