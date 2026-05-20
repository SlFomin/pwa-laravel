<?php

namespace SlFomin\PwaLaravel;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use SlFomin\PwaLaravel\Commands\PwaLaravelCommand;

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
