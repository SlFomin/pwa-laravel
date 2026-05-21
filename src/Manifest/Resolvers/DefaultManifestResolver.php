<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Manifest\Resolvers;

use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Contracts\ManifestResolver;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

final class DefaultManifestResolver implements ManifestResolver
{
    public function resolve(Request $request, ManifestBuilder $default): ManifestBuilder
    {
        return $default;
    }

    public function cacheKey(Request $request): ?string
    {
        return 'default';
    }
}
