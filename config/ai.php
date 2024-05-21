<?php

return [
    'driver' => env('AI_DRIVER', 'openai'),
    'model' => env('AI_MODEL'),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],
];
