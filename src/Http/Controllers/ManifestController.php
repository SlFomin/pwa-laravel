<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SlFomin\PwaLaravel\Contracts\ManifestDriver;

final class ManifestController
{
    public function __invoke(Request $request, ManifestDriver $driver): Response
    {
        $manifest = $driver->resolve($request);

        return response(
            $manifest->toJson(),
            200,
            config('pwa.headers.manifest', []),
        );
    }
}
