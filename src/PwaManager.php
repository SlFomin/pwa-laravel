<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel;

use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Contracts\ManifestDriver;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;
use SlFomin\PwaLaravel\ServiceWorker\WorkerManager;

final class PwaManager
{
    public function __construct(
        protected readonly ManifestDriver $manifestDriver,
        protected readonly WorkerManager $worker,
    ) {}

    public function manifest(?Request $request = null): ManifestBuilder
    {
        return $this->manifestDriver->resolve($request ?? request());
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
