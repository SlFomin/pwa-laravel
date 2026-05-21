<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Events;

use Illuminate\Http\Request;

final class ManifestResolving
{
    public function __construct(
        public readonly Request $request,
    ) {}
}
