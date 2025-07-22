<?php

namespace Nexxtmove\Tests;

use Nexxtmove\AI\AIServiceProvider;
use Orchestra\Testbench\TestCase as TestbenchTestCase;

class TestCase extends TestbenchTestCase
{
    protected function getPackageProviders($app)
    {
        return [AIServiceProvider::class];
    }
}
