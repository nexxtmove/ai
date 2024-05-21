<?php

namespace Nexxtmove;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Nexxtmove\Common\Commands\AICommand;
use Nexxtmove\Drivers\AIDriver;
use Nexxtmove\Drivers\Gemini;
use Nexxtmove\Drivers\OpenAI;

class AIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ai.php', 'ai');

        $this->app->bind(AIDriver::class, function (Application $app) {
            $driver = match (config('ai.driver')) {
                'openai' => OpenAI::class,
                'gemini' => Gemini::class,
            };

            return $app->make($driver);
        });
    }

    public function boot(): void
    {
        $this->commands([
            AICommand::class,
        ]);
    }
}
