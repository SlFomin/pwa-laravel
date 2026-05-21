<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Events;

use Illuminate\Http\Request;

final class ServiceWorkerRequested
{
    public function __construct(
        public readonly Request $request,
        public readonly string $path,
        public readonly string $url,
    ) {}
}
