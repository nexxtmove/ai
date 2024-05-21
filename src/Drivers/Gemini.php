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

        $response = $this->http
            ->withQueryParameters(['key' => config('ai.gemini.api_key')])
            ->post('models/'.$model.':generateContent', [
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
