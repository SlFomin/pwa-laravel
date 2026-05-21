<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Contracts\ManifestDriver;
use SlFomin\PwaLaravel\Events\ManifestResolved;
use SlFomin\PwaLaravel\Events\ManifestResolving;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;
use SlFomin\PwaLaravel\ServiceWorker\WorkerManager;

final class PwaManager
{
    public function __construct(
        protected readonly ManifestDriver $manifestDriver,
        protected readonly WorkerManager $worker,
        protected readonly Dispatcher $events,
    ) {}

    public function manifest(?Request $request = null): ManifestBuilder
    {
        $request ??= request();

        $this->events->dispatch(new ManifestResolving($request));

        $manifest = $this->manifestDriver->resolve($request);

        $this->events->dispatch(new ManifestResolved($request, $manifest));

        return $manifest;
    }

    public function manifestUrl(?Request $request = null): string
    {
        return $this->manifestDriver->url($request ?? request());
    }

    public function serviceWorkerUrl(): string
    {
        return $this->worker->registrationUrl();
    }

    public function worker(): WorkerManager
    {
        return $this->worker;
    }

    public function driver(): ManifestDriver
    {
        return $this->manifestDriver;
    }
}
