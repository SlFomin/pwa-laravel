<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Manifest\Drivers;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Contracts\ManifestDriver;
use SlFomin\PwaLaravel\Contracts\ManifestResolver;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutDiscoverer;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

final class DynamicManifestDriver implements ManifestDriver
{
    public function __construct(
        protected readonly ManifestResolver $resolver,
        protected readonly CacheFactory $cacheFactory,
        protected readonly ShortcutDiscoverer $shortcuts,
    ) {}

    public function resolve(Request $request): ManifestBuilder
    {
        $default = ManifestBuilder::make(config('pwa.manifest.data', []));

        if (! config('pwa.manifest.dynamic.cache', true)) {
            return $this->injectShortcuts($this->resolver->resolve($request, $default));
        }

        $cacheKey = $this->resolver->cacheKey($request);
        if ($cacheKey === null) {
            return $this->injectShortcuts($this->resolver->resolve($request, $default));
        }

        $fullKey = config('pwa.manifest.dynamic.cache_key_prefix', 'pwa.manifest.').$cacheKey;
        $ttl = (int) config('pwa.manifest.dynamic.cache_ttl', 3600);
        $store = config('pwa.manifest.dynamic.cache_store');

        return $this->cacheFactory->store($store)->remember(
            $fullKey,
            $ttl,
            fn (): ManifestBuilder => $this->injectShortcuts($this->resolver->resolve($request, $default)),
        );
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
            'crossorigin' => 'use-credentials',
        ];
    }

    private function injectShortcuts(ManifestBuilder $builder): ManifestBuilder
    {
        if (! config('pwa.shortcuts.enabled', true)) {
            return $builder;
        }

        $existing = $builder->get('shortcuts', []);
        if (is_array($existing) && $existing !== []) {
            return $builder;
        }

        $collection = $this->shortcuts->discover();
        if ($collection->isEmpty()) {
            return $builder;
        }

        return $builder->shortcuts($collection->toManifestArray());
    }
}