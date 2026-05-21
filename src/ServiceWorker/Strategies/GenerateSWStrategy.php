<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\ServiceWorker\Strategies;

use SlFomin\PwaLaravel\Contracts\ServiceWorkerStrategy;

final class GenerateSWStrategy implements ServiceWorkerStrategy
{
    public function path(): string
    {
        return public_path(ltrim($this->url(), '/'));
    }

    public function url(): string
    {
        return config('pwa.service_worker.url', '/sw.js');
    }

    public function exists(): bool
    {
        return file_exists($this->path());
    }

    public function viteOptions(): array
    {
        return [
            'strategies' => 'generateSW',
            'filename' => ltrim($this->url(), '/'),
        ];
    }
}
