<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Contracts;

interface ServiceWorkerStrategy
{
    /** Absolute filesystem path to the built service worker file. */
    public function path(): string;

    /** Public URL path served to the browser (e.g. "/sw.js"). */
    public function url(): string;

    /** Whether the service worker file has been built and exists on disk. */
    public function exists(): bool;

    /**
     * Partial Workbox/VitePWA options merged into the laravelPwa() Vite plugin.
     *
     * Must include "strategies" and "filename" at minimum.
     * Keys are merged shallowly — deep Workbox options must be set by the strategy.
     *
     * @return array<string, mixed>
     */
    public function viteOptions(): array;
}
