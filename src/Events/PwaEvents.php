<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Events;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Event;
use SlFomin\PwaLaravel\Http\Controllers\ServiceWorkerController;

/**
 * Fluent helper for registering PWA event listeners.
 *
 * @see ManifestResolving
 * @see ManifestResolved
 * @see ServiceWorkerRequested
 * @see IconsGenerated
 * @see ManifestPublished
 */
final class PwaEvents
{
    /**
     * Fires before the active driver resolves the manifest.
     *
     * The listener receives a {@see ManifestResolving} instance with
     * the request and the default manifest (from `pwa.manifest.data`).
     *
     * @param  callable(ManifestResolving): void  $listener
     */
    public static function manifestResolving(callable $listener): void
    {
        self::dispatcher()->listen(ManifestResolving::class, $listener);
    }

    /**
     * Fires after the manifest has been resolved, before serialization.
     *
     * The listener receives a {@see ManifestResolved} instance whose
     * {@see ManifestBuilder} can be mutated for last-chance modification.
     *
     * @param  callable(ManifestResolved): void  $listener
     */
    public static function manifestResolved(callable $listener): void
    {
        self::dispatcher()->listen(ManifestResolved::class, $listener);
    }

    /**
     * Fires when the Service Worker file is served via {@see ServiceWorkerController}.
     *
     * @param  callable(ServiceWorkerRequested): void  $listener
     */
    public static function serviceWorkerRequested(callable $listener): void
    {
        self::dispatcher()->listen(ServiceWorkerRequested::class, $listener);
    }

    /**
     * Fires after `pwa:generate-icons` finishes writing the icon set.
     *
     * @param  callable(IconsGenerated): void  $listener
     */
    public static function iconsGenerated(callable $listener): void
    {
        self::dispatcher()->listen(IconsGenerated::class, $listener);
    }

    /**
     * Fires after `pwa:publish-manifest` writes the manifest file.
     *
     * @param  callable(ManifestPublished): void  $listener
     */
    public static function manifestPublished(callable $listener): void
    {
        self::dispatcher()->listen(ManifestPublished::class, $listener);
    }

    private static function dispatcher(): Dispatcher
    {
        return Event::getFacadeRoot();
    }
}
