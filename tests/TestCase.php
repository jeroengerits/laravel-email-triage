<?php

declare(strict_types=1);

namespace JeroenGerits\EmailTriage\Tests;

use JeroenGerits\EmailTriage\EmailTriageServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [EmailTriageServiceProvider::class];
    }
}
