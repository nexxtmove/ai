<?php

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Nexxtmove\AI\Drivers\OpenAI;
use Nexxtmove\AI\Schema;
use Nexxtmove\AI\Tool;

test('ask calls OpenAI API', function () {
    Http::fake();

    $ai = new OpenAI();

    $ai->ask('How are you?');

    Http::assertSent(function (Request $request) {
        return $request->url() === 'https://api.openai.com/v1/chat/completions'
            && $request['model'] === 'gpt-3.5-turbo';
    });
});

test('sends question', function () {
    Http::fake();

    $ai = new OpenAI();

    $ai->ask('How are you?');

    Http::assertSent(function (Request $request) {
        return $request['messages'][0]['content'] === 'How are you?';
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

    $ai = new OpenAI();

    $response = $ai->ask('How are you?');

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

    $ai = new OpenAI();

    $ai->ask('How are you?');
})->throws('API key expired.');


test('specify which model to use', function () {
    Http::fake();

    $ai = new OpenAI();

    $ai->ask('How are you?', [
        'model' => 'gpt-4.1',
    ]);

    Http::assertSent(function (Request $request) {
        return $request['model'] === 'gpt-4.1';
    });
});

test('message history is NOT persisted by default', function () {
    Http::fake();

    $ai = new OpenAI();

    $ai->ask('What is 1+1');
    $ai->ask('And that result multiplied by 2?');
});

test('conversation persistence', function () {
    Http::fake();

    $ai1 = new OpenAI();

    $ai1->ask('What is 1+1', [
        'conversation_id' => 'test-conversation',
    ]);

    $ai2 = new OpenAI();

    $ai2->ask('And that result multiplied by 2?', [
        'conversation_id' => 'test-conversation',
    ]);

    Http::assertSent(function (Request $request) {
        return $request['messages'] === [
            ['role' => 'user', 'content' => 'What is 1+1'],
            ['role' => 'assistant', 'content' => '2'],
            ['role' => 'user', 'content' => 'And that result multiplied by 2?'],
        ];
    });
});

test('tool calling', function () {
    $ai = new OpenAI();

    $weatherTool = new class implements Tool {
        public function name(): string
        {
            return 'weather';
        }

        public function description(): string
        {
            return 'Get the current weather.';
        }

        public function parameters(): array
        {
            return [
                'city' => Schema::string(['description' => 'The city to get the weather for.']),
                'unit' => Schema::enum(['celsius', 'fahrenheit'], ['default' => 'celsius']),
            ];
        }

        public function handle(array $parameters): string
        {
            return  '60 degrees and sunny.'; // Simulated response
        }
    };

    $response = $ai->ask('What is the weather in New York?', [
        'tools' => [$weatherTool],
    ]);

    expect($response)->toBe('60 degrees and sunny.');
});
