<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel;

use SlFomin\PwaLaravel\Blade\PwaDirectives;
use SlFomin\PwaLaravel\Console\GenerateIconsCommand;
use SlFomin\PwaLaravel\Contracts\IconGenerator;
use SlFomin\PwaLaravel\Contracts\ManifestDriver;
use SlFomin\PwaLaravel\Contracts\ManifestResolver;
use SlFomin\PwaLaravel\Contracts\ServiceWorkerStrategy;
use SlFomin\PwaLaravel\Http\Middleware\PwaHeaders;
use SlFomin\PwaLaravel\Manifest\Drivers\DynamicManifestDriver;
use SlFomin\PwaLaravel\Manifest\Drivers\StaticManifestDriver;
use SlFomin\PwaLaravel\Manifest\IconProcessor;
use SlFomin\PwaLaravel\Manifest\Resolvers\DefaultManifestResolver;
use SlFomin\PwaLaravel\ServiceWorker\Strategies\GenerateSWStrategy;
use SlFomin\PwaLaravel\ServiceWorker\Strategies\InjectManifestStrategy;
use SlFomin\PwaLaravel\ServiceWorker\ViteManifestBridge;
use SlFomin\PwaLaravel\ServiceWorker\WorkerManager;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PwaLaravelServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('pwa-laravel')
            ->hasConfigFile('pwa')
            ->hasViews('pwa')
            ->hasRoute('pwa')
            ->hasCommands([
                GenerateIconsCommand::class,
            ])
            ->hasInstallCommand(function (InstallCommand $command): void {
                $command
                    ->publishConfigFile()
                    ->publishAssets()
                    ->startWith(function (InstallCommand $cmd): void {
                        $cmd->info('Welcome to Laravel Vite PWA installer.');
                    })
                    ->endWith(function (InstallCommand $cmd): void {
                        $this->printPostInstallInstructions($cmd);
                    })
                    ->askToStarRepoOnGitHub('slfomin/pwa-laravel');
            });
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(WorkerManager::class);
        $this->app->singleton(ViteManifestBridge::class);
        $this->app->singleton(PwaManager::class);

        $this->app->bind(IconGenerator::class, IconProcessor::class);

        $this->app->bind(ServiceWorkerStrategy::class, function ($app) {
            return match (config('pwa.service_worker.strategy', 'generateSW')) {
                'injectManifest' => $app->make(InjectManifestStrategy::class),
                default => $app->make(GenerateSWStrategy::class),
            };
        });

        $this->app->bind(ManifestResolver::class, function ($app) {
            $class = config('pwa.manifest.dynamic.resolver', DefaultManifestResolver::class);

            return $app->make($class);
        });

        $this->app->bind(ManifestDriver::class, function ($app) {
            return match (config('pwa.manifest.driver', 'static')) {
                'static' => $app->make(StaticManifestDriver::class),
                'dynamic' => $app->make(DynamicManifestDriver::class),
                default => throw new \InvalidArgumentException(
                    'Unknown manifest driver: '.config('pwa.manifest.driver')
                ),
            };
        });

        $this->app->alias(PwaManager::class, 'pwa');
    }

    public function packageBooted(): void
    {
        PwaDirectives::register();

        $this->app->make('router')->aliasMiddleware('pwa.headers', PwaHeaders::class);
    }

    private function printPostInstallInstructions(InstallCommand $command): void
    {
        $command->newLine();
        $command->info('Config published to config/pwa.php');
        $command->newLine();
        $command->line('<fg=cyan>Next steps:</>');
        $command->line('  1. Place 512x512+ PNG at resources/images/pwa-icon.png');
        $command->line('  2. ddev artisan pwa:generate-icons');
        $command->line('  3. Add @pwaMeta to your layout <head>');
        $command->line('  4. Add @pwaRegisterSW before </body>');
        $command->line('  5. ddev npm install -D vite-plugin-pwa @slfomin/pwa-laravel');
        $command->line('  6. ddev npm run build');
    }
}
