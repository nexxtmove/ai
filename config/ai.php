<?php

return [
    'driver' => env('AI_DRIVER', 'openai'),
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'default_model' => env('AI_MODEL', 'gpt-4.1'),
    ],
];
