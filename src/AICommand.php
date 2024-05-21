<?php

namespace Nexxtmove;

use Illuminate\Console\Command;

class AICommand extends Command
{
    protected $signature = 'ai:ask {prompt}';

    public function handle()
    {
        $prompt = $this->argument('job');

        $response = AI::ask($prompt);

        $this->info($response);
    }
}
