<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Http\Controllers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use SlFomin\PwaLaravel\Contracts\ServiceWorkerStrategy;
use SlFomin\PwaLaravel\Events\ServiceWorkerRequested;

final class ServiceWorkerController
{
    public function __construct(
        private readonly ServiceWorkerStrategy $strategy,
        private readonly Dispatcher $events,
    ) {}

    public function __invoke(Request $request): Response
    {
        if (! $this->strategy->exists()) {
            abort(404, 'Service worker not found.');
        }

        $this->events->dispatch(new ServiceWorkerRequested(
            $request,
            $this->strategy->path(),
            $this->strategy->url(),
        ));

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
