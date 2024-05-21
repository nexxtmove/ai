<?php

namespace Nexxtmove\Drivers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class Gemini implements AIDriver
{
    private PendingRequest $http;

    public function __construct()
    {
        $this->http = Http::baseUrl('https://generativelanguage.googleapis.com/v1');
    }

    public function ask(string $prompt): ?string
    {
        $model = config('ai.model') ?: 'gemini-pro';
        $key = config('ai.gemini.api_key');

        $response = $this->http->post("models/{$model}:generateContent?key={$key}", [
            'contents' => [
                [
                    'parts' => [
                        ['role' => 'user', 'text' => $prompt],
                    ],
                ],
            ],
        ]);

        return $response->json('candidates.0.content.parts.0.text');
    }
}
