<?php

namespace Nexxtmove;

use Illuminate\Support\ServiceProvider;

class FractalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->alias('ai', AIIntegration::class);

        $this->mergeConfigFrom(
            __DIR__.'/../config/ai.php',
            'ai'
        );
    }
}
