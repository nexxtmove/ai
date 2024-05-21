<?php

namespace Nexxtmove\Common\Commands;

use Illuminate\Console\Command;
use Nexxtmove\AI;

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
