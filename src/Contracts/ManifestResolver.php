<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Contracts;

use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

interface ManifestResolver
{
    /**
     * Enrich or replace the default manifest builder for the given request.
     *
     * May mutate and return `$default`, or return a new ManifestBuilder.
     * Should NOT throw on missing locale/tenant data — return `$default` unchanged.
     * Called once per cache-miss; must be side-effect-free (safe for Octane/Swoole).
     */
    public function resolve(Request $request, ManifestBuilder $default): ManifestBuilder;

    /**
     * Cache key for the resolved manifest, or null to skip caching.
     *
     * Must be deterministic: same request → same key.
     * Return null for requests whose manifest should never be cached
     * (e.g. per-user dynamic content or A/B tested manifests).
     */
    public function cacheKey(Request $request): ?string;
}
