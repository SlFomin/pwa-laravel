<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use SlFomin\PwaLaravel\Http\Controllers\ManifestController;

$route = config('pwa.manifest.route', '/manifest.webmanifest');

Route::get($route, ManifestController::class)
    ->middleware(['web', 'pwa.headers'])
    ->name('pwa.manifest');
