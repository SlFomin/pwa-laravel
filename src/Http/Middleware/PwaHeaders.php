<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class PwaHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $path = '/'.ltrim($request->path(), '/');

        if ($this->isServiceWorker($path)) {
            $this->applyHeaders($response, config('pwa.headers.service_worker', []));
        }

        if ($this->isManifest($path)) {
            $this->applyHeaders($response, config('pwa.headers.manifest', []));
        }

        return $response;
    }

    private function isServiceWorker(string $path): bool
    {
        return str_ends_with($path, '/sw.js')
            || str_ends_with($path, '/service-worker.js')
            || $path === config('pwa.service_worker.url', '/sw.js');
    }

    private function isManifest(string $path): bool
    {
        return str_ends_with($path, '/manifest.webmanifest')
            || str_ends_with($path, '/manifest.json')
            || $path === config('pwa.manifest.route', '/manifest.webmanifest');
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function applyHeaders(Response $response, array $headers): void
    {
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }
    }
}
