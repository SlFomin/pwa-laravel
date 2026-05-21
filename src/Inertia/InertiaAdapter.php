<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Inertia;

use Inertia\Inertia;
use SlFomin\PwaLaravel\Contracts\ManifestDriver;
use SlFomin\PwaLaravel\ServiceWorker\WorkerManager;

final class InertiaAdapter
{
    public function __construct(
        protected readonly ManifestDriver $manifestDriver,
        protected readonly WorkerManager $worker,
    ) {}

    public function boot(): void
    {
        if (! config('pwa.inertia.share_props', true)) {
            return;
        }

        if (! InertiaDetector::installed()) {
            return;
        }

        $key = config('pwa.inertia.shared_prop_key', 'pwa');

        Inertia::share($key, fn (): array => [
            'manifest_url' => $this->manifestDriver->url(request()),
            'sw' => [
                'url' => $this->worker->registrationUrl(),
                'scope' => $this->worker->scope(),
                'register_type' => $this->worker->registerType(),
                'auto_register' => $this->worker->isAutoRegister(),
                'available' => $this->worker->isAvailable(),
            ],
            'navigate_fallback' => config('pwa.inertia.navigate_fallback'),
            'is_ssr' => InertiaDetector::isSsr(),
        ]);
    }
}
