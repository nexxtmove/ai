<?php

namespace Nexxtmove\Drivers;

interface AIDriver
{
    public function ask(string $prompt): ?string;
}
