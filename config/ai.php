<?php

return [
    'driver' => Nexxtmove\Drivers\OpenAI::class,

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],
];
