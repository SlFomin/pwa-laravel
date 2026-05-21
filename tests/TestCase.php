<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SlFomin\PwaLaravel\PwaLaravelServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            PwaLaravelServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
    }
}
