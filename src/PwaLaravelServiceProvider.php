<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel;

use SlFomin\PwaLaravel\Blade\PwaDirectives;
use SlFomin\PwaLaravel\Console\GenerateIconsCommand;
use SlFomin\PwaLaravel\Console\PublishManifestCommand;
use SlFomin\PwaLaravel\Contracts\IconGenerator;
use SlFomin\PwaLaravel\Contracts\ManifestDriver;
use SlFomin\PwaLaravel\Contracts\ManifestResolver;
use SlFomin\PwaLaravel\Contracts\ServiceWorkerStrategy;
use SlFomin\PwaLaravel\Core\Shortcuts\DefaultIconResolver;
use SlFomin\PwaLaravel\Core\Shortcuts\FilesystemIconMetadataProbe;
use SlFomin\PwaLaravel\Core\Shortcuts\IconMetadataProbe;
use SlFomin\PwaLaravel\Core\Shortcuts\IconResolver;
use SlFomin\PwaLaravel\Core\Shortcuts\IconSetRegistry;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutDiscoverer;
use SlFomin\PwaLaravel\Http\Middleware\PwaHeaders;
use SlFomin\PwaLaravel\Inertia\InertiaAdapter;
use SlFomin\PwaLaravel\Inertia\InertiaDetector;
use SlFomin\PwaLaravel\Inertia\InertiaPwaMiddleware;
use SlFomin\PwaLaravel\Laravel\Console\IconSetsListCommand;
use SlFomin\PwaLaravel\Laravel\Console\ShortcutsCacheCommand;
use SlFomin\PwaLaravel\Laravel\Console\ShortcutsClearCommand;
use SlFomin\PwaLaravel\Laravel\Console\ShortcutsListCommand;
use SlFomin\PwaLaravel\Laravel\Shortcuts\AttributeIconSetRegistry;
use SlFomin\PwaLaravel\Laravel\Shortcuts\CachedDiscoverer;
use SlFomin\PwaLaravel\Laravel\Shortcuts\CompositeIconSetRegistry;
use SlFomin\PwaLaravel\Laravel\Shortcuts\ConfigIconSetRegistry;
use SlFomin\PwaLaravel\Laravel\Shortcuts\RouteAttributeDiscoverer;
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
                PublishManifestCommand::class,
                IconSetsListCommand::class,
                ShortcutsCacheCommand::class,
                ShortcutsClearCommand::class,
                ShortcutsListCommand::class,
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

        $this->app->bind(
            IconMetadataProbe::class,
            fn ($app) => new FilesystemIconMetadataProbe($app['path.public']),
        );

        $this->app->bind(
            ConfigIconSetRegistry::class,
            fn ($app) => new ConfigIconSetRegistry(
                $app['config'],
            ),
        );

        $this->app->singleton(
            AttributeIconSetRegistry::class,
        );

        $this->app->bind(IconSetRegistry::class, function ($app) {
            return new CompositeIconSetRegistry([
                $app->make(AttributeIconSetRegistry::class),
                $app->make(ConfigIconSetRegistry::class),
            ]);
        });

        $this->app->bind(IconResolver::class, function ($app) {
            return new DefaultIconResolver(
                $app->make(IconMetadataProbe::class),
                $app->make(IconSetRegistry::class),
            );
        });

        $this->app->bind(RouteAttributeDiscoverer::class, function ($app) {
            return new RouteAttributeDiscoverer(
                $app['router']->getRoutes(),
                $app->make(IconResolver::class),
            );
        });

        $this->app->scoped(ShortcutDiscoverer::class, function ($app) {
            $base = new RouteAttributeDiscoverer(
                $app['router']->getRoutes(),
                $app->make(IconResolver::class),
            );

            $cacheEnabled = $app['config']->get('pwa.shortcuts.cache_enabled');
            if ($cacheEnabled === null) {
                $cacheEnabled = $app->environment('production');
            }

            if (! $cacheEnabled) {
                return $base;
            }

            return new CachedDiscoverer($base, $app['cache']->store());
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
        $this->app->make('router')->aliasMiddleware('pwa.inertia', InertiaPwaMiddleware::class);

        if (InertiaDetector::installed() && config('pwa.inertia.auto_detect', true)) {
            $this->app->make(InertiaAdapter::class)->boot();
        }
    }

    private function printPostInstallInstructions(InstallCommand $command): void
    {
        $command->newLine();
        $command->info('Config published to config/pwa.php');
        $command->newLine();
        $command->line('<fg=cyan>Next steps:</>');
        $command->line('  1. Place a 512×512 PNG at <info>resources/images/pwa-icon.png</info>');
        $command->line('  2. Run <info>ddev artisan pwa:generate-icons</info> to create icon set');
        $command->line('  3. Add <info>@pwaMeta</info> inside <head> in your layout');
        $command->line('  4. Add <info>@pwaRegisterSW</info> before </body> in your layout');
        $command->line('  5. Install JS dependencies:');
        $command->line('       <info>ddev npm install -D vite-plugin-pwa @slfomin/pwa-laravel</info>');
        $command->line('  6. Update <info>vite.config.js</info> — see README for the laravelPwa() plugin setup');
        $command->line('  7. Build assets: <info>ddev npm run build</info>');
        $command->newLine();
        $command->line('<fg=yellow>Without Vite?</> Run <info>ddev artisan pwa:publish-manifest</info> to generate manifest from config.');
    }
}
