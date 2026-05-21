<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Contracts;

use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

interface ManifestResolver
{
    public function resolve(Request $request, ManifestBuilder $default): ManifestBuilder;

    public function cacheKey(Request $request): ?string;
}
