<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Blade;

use Illuminate\Support\Facades\Blade;

final class PwaDirectives
{
    public static function register(): void
    {
        Blade::directive('pwaMeta', function (): string {
            return <<<'PHP'
                <?php
                    $__pwaDriver = app(\SlFomin\PwaLaravel\Contracts\ManifestDriver::class);
                    $__pwaRequest = request();
                    echo view('pwa::directives.meta', [
                        'manifest' => $__pwaDriver->resolve($__pwaRequest),
                        'manifestLink' => $__pwaDriver->linkAttributes($__pwaRequest),
                    ])->render();
                ?>
                PHP;
        });

        Blade::directive('pwaRegisterSW', function (): string {
            return <<<'PHP'
                <?php
                    echo view('pwa::directives.sw-register', [
                        'worker' => app(\SlFomin\PwaLaravel\ServiceWorker\WorkerManager::class),
                    ])->render();
                ?>
                PHP;
        });

        Blade::directive('pwaInstallButton', function (string $expression): string {
            $expression = trim($expression) ?: "''";

            return <<<PHP
                <?php
                    echo view('pwa::directives.install-button', [
                        'text' => {$expression} ?: 'Install app',
                    ])->render();
                ?>
                PHP;
        });
    }
}
