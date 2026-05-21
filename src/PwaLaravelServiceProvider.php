<?php

namespace SlFomin\PwaLaravel;

use SlFomin\PwaLaravel\Commands\PwaLaravelCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PwaLaravelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('pwa-laravel')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_pwa_laravel_table')
            ->hasCommand(PwaLaravelCommand::class);
    }
}
