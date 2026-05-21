<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Manifest\Drivers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SlFomin\PwaLaravel\Contracts\ManifestDriver;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

final class StaticManifestDriver implements ManifestDriver
{
    public function resolve(Request $request): ManifestBuilder
    {
        $path = config('pwa.manifest.static_path');

        if (! is_string($path) || ! file_exists($path)) {
            return ManifestBuilder::make(config('pwa.manifest.data', []));
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return ManifestBuilder::make(config('pwa.manifest.data', []));
        }

        $data = json_decode($contents, true);
        if (! is_array($data)) {
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning('[PWA] manifest.webmanifest contains invalid JSON: '.json_last_error_msg(), [
                    'path' => $path,
                ]);
            }

            return ManifestBuilder::make(config('pwa.manifest.data', []));
        }

        return ManifestBuilder::make($data);
    }

    public function url(Request $request): string
    {
        return config('pwa.manifest.route', '/manifest.webmanifest');
    }

    public function linkAttributes(Request $request): array
    {
        return [
            'rel' => 'manifest',
            'href' => $this->url($request),
        ];
    }
}
