<?php

namespace Nexxtmove\Drivers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class OpenAI implements AIDriver
{
    private PendingRequest $http;

    public function __construct()
    {
        $this->http = Http::baseUrl('https://api.openai.com/v1')
            ->withToken(config('ai.openai.api_key'));
    }

    public function ask(string $prompt): ?string
    {
        $model = config('ai.model') ?: 'gpt-3.5-turbo';

        $response = $this->http->post('chat/completions', [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        $response->throw();

        return $response->json('choices.0.message.content');
    }
}
