<?php

namespace Nexxtmove;

use Illuminate\Support\ServiceProvider;
use Nexxtmove\Drivers\AIDriver;

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/ai.php',
            'ai'
        );

        $this->app->bind(AIDriver::class, config('ai.driver'));
    }
}
