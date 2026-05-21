<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Facades;

use Illuminate\Support\Facades\Facade;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;
use SlFomin\PwaLaravel\PwaManager;
use SlFomin\PwaLaravel\ServiceWorker\WorkerManager;

/**
 * @method static ManifestBuilder manifest(?\Illuminate\Http\Request $request = null)
 * @method static string manifestUrl(?\Illuminate\Http\Request $request = null)
 * @method static string serviceWorkerUrl()
 * @method static WorkerManager worker()
 * @method static \SlFomin\PwaLaravel\Contracts\ManifestDriver driver()
 *
 * @see PwaManager
 */
final class Pwa extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PwaManager::class;
    }
}
