<?php

return [
    'driver' => env('AI_DRIVER', 'openai'),
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],
];
