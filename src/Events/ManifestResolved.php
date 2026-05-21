<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Events;

use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

final class ManifestResolved
{
    public function __construct(
        public readonly Request $request,
        public readonly ManifestBuilder $manifest,
    ) {}
}
