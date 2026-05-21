<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use SlFomin\PwaLaravel\Http\Controllers\ManifestController;
use SlFomin\PwaLaravel\Http\Controllers\ServiceWorkerController;

$route = config('pwa.manifest.route', '/manifest.webmanifest');

Route::get($route, ManifestController::class)
    ->middleware(['web', 'pwa.headers'])
    ->name('pwa.manifest');

Route::get(config('pwa.service_worker.url', '/sw.js'), ServiceWorkerController::class)
    ->middleware(['pwa.headers'])
    ->name('pwa.sw');
