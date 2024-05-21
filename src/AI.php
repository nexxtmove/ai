<?php

namespace Nexxtmove;

use Illuminate\Support\Facades\Facade;
use Nexxtmove\Drivers\AIDriver;

class AI extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AIDriver::class;
    }
}
