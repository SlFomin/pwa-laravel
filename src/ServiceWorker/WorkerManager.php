<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\ServiceWorker;

final class WorkerManager
{
    public function __construct(
        protected readonly ViteManifestBridge $vite,
    ) {}

    public function registrationUrl(): string
    {
        return config('pwa.service_worker.url', '/sw.js');
    }

    public function scope(): string
    {
        return config('pwa.service_worker.scope', '/');
    }

    public function registerType(): string
    {
        return config('pwa.service_worker.register_type', 'autoUpdate');
    }

    public function isAutoRegister(): bool
    {
        return (bool) config('pwa.service_worker.auto_register', true);
    }

    public function shouldRegisterInDev(): bool
    {
        return (bool) config('pwa.service_worker.dev_enabled', false);
    }

    public function isAvailable(): bool
    {
        return $this->vite->exists() || file_exists(public_path(ltrim($this->registrationUrl(), '/')));
    }

    public function registrationScript(): string
    {
        $url = json_encode($this->registrationUrl(), JSON_UNESCAPED_SLASHES);
        $scope = json_encode($this->scope(), JSON_UNESCAPED_SLASHES);
        $autoUpdate = $this->registerType() === 'autoUpdate' ? 'true' : 'false';

        return <<<JS
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function () {
                    navigator.serviceWorker.register({$url}, { scope: {$scope} })
                        .then(function (reg) {
                            if ({$autoUpdate}) {
                                reg.addEventListener('updatefound', function () {
                                    var nw = reg.installing;
                                    if (!nw) return;
                                    nw.addEventListener('statechange', function () {
                                        if (nw.state === 'installed' && navigator.serviceWorker.controller) {
                                            nw.postMessage({ type: 'SKIP_WAITING' });
                                        }
                                    });
                                });
                            }
                            window.dispatchEvent(new CustomEvent('pwa:registered', { detail: { registration: reg } }));
                        })
                        .catch(function (err) {
                            console.error('[PWA] SW registration failed:', err);
                            window.dispatchEvent(new CustomEvent('pwa:error', { detail: { error: err } }));
                        });
                });
            }
            JS;
    }
}
