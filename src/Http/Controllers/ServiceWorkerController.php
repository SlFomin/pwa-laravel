<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Http\Controllers;

use Illuminate\Http\Response;
use SlFomin\PwaLaravel\Contracts\ServiceWorkerStrategy;

final class ServiceWorkerController
{
    public function __construct(
        private readonly ServiceWorkerStrategy $strategy,
    ) {}

    public function __invoke(): Response
    {
        if (! $this->strategy->exists()) {
            abort(404, 'Service worker not found.');
        }

        return new Response(
            file_get_contents($this->strategy->path()),
            200,
            [
                'Content-Type' => 'application/javascript',
                'Service-Worker-Allowed' => config('pwa.service_worker.scope', '/'),
            ],
        );
    }
}
