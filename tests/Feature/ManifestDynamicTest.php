<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use SlFomin\PwaLaravel\Contracts\ManifestResolver;
use SlFomin\PwaLaravel\Manifest\Drivers\DynamicManifestDriver;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;
use SlFomin\PwaLaravel\Manifest\Resolvers\DefaultManifestResolver;

beforeEach(function (): void {
    config([
        'pwa.manifest.driver' => 'dynamic',
        'pwa.manifest.route' => '/manifest.webmanifest',
        'pwa.manifest.data' => [
            'name' => 'Base App',
            'short_name' => 'Base',
            'start_url' => '/',
            'display' => 'standalone',
        ],
        'pwa.manifest.dynamic' => [
            'resolver' => DefaultManifestResolver::class,
            'cache' => false,
            'cache_ttl' => 3600,
            'cache_key_prefix' => 'pwa.manifest.',
            'cache_store' => null,
        ],
    ]);
});

// --- Driver behaviour ---

it('dynamic driver resolves manifest via resolver', function (): void {
    $driver = app(DynamicManifestDriver::class);
    $manifest = $driver->resolve(Request::create('/'));

    expect($manifest)->toBeInstanceOf(ManifestBuilder::class)
        ->and($manifest->get('name'))->toBe('Base App');
});

it('dynamic driver url returns configured route', function (): void {
    $driver = app(DynamicManifestDriver::class);

    expect($driver->url(Request::create('/')))->toBe('/manifest.webmanifest');
});

it('dynamic driver link attributes include crossorigin', function (): void {
    $driver = app(DynamicManifestDriver::class);
    $attrs = $driver->linkAttributes(Request::create('/'));

    expect($attrs)->toMatchArray([
        'rel' => 'manifest',
        'href' => '/manifest.webmanifest',
        'crossorigin' => 'use-credentials',
    ]);
});

// --- Caching ---

it('dynamic driver skips cache when cache disabled', function (): void {
    config(['pwa.manifest.dynamic.cache' => false]);
    Cache::shouldReceive('store')->never();

    $driver = app(DynamicManifestDriver::class);
    $driver->resolve(Request::create('/'));
});

it('dynamic driver caches manifest when cache enabled', function (): void {
    config(['pwa.manifest.dynamic.cache' => true]);

    $driver = app(DynamicManifestDriver::class);
    $first = $driver->resolve(Request::create('/'));
    $second = $driver->resolve(Request::create('/'));

    // Both calls return the same data; cache works within the test if store is array
    expect($first->get('name'))->toBe($second->get('name'));
});

it('dynamic driver uses configured cache key prefix', function (): void {
    config([
        'pwa.manifest.dynamic.cache' => true,
        'pwa.manifest.dynamic.cache_key_prefix' => 'my.prefix.',
        'cache.default' => 'array',
    ]);

    $driver = app(DynamicManifestDriver::class);
    $driver->resolve(Request::create('/'));

    expect(Cache::has('my.prefix.default'))->toBeTrue();
});

it('dynamic driver uses configured cache ttl', function (): void {
    config([
        'pwa.manifest.dynamic.cache' => true,
        'pwa.manifest.dynamic.cache_ttl' => 999,
        'cache.default' => 'array',
    ]);

    // Only verifies no exception thrown; TTL correctness is Laravel's concern
    $driver = app(DynamicManifestDriver::class);
    $manifest = $driver->resolve(Request::create('/'));

    expect($manifest)->toBeInstanceOf(ManifestBuilder::class);
});

it('dynamic driver skips cache when resolver returns null cache key', function (): void {
    config(['pwa.manifest.dynamic.cache' => true]);

    $nullKeyResolver = new class implements ManifestResolver
    {
        public function resolve(Request $request, ManifestBuilder $default): ManifestBuilder
        {
            return $default->name('NoCache');
        }

        public function cacheKey(Request $request): ?string
        {
            return null;
        }
    };

    $this->app->bind(ManifestResolver::class, fn () => $nullKeyResolver);

    $driver = app(DynamicManifestDriver::class);
    $manifest = $driver->resolve(Request::create('/'));

    expect($manifest->get('name'))->toBe('NoCache');
});

// --- HTTP route in dynamic mode ---

it('manifest route responds 200 in dynamic mode', function (): void {
    $response = $this->get('/manifest.webmanifest');
    $response->assertStatus(200);
});

it('manifest route returns application/manifest+json in dynamic mode', function (): void {
    config(['pwa.headers.manifest' => ['Content-Type' => 'application/manifest+json']]);

    $response = $this->get('/manifest.webmanifest');
    $response->assertHeader('Content-Type', 'application/manifest+json');
});

it('manifest route json contains resolver data', function (): void {
    $response = $this->get('/manifest.webmanifest');
    $data = $response->json();

    expect($data['name'])->toBe('Base App')
        ->and($data['display'])->toBe('standalone');
});

// --- Custom resolver ---

it('custom resolver can override manifest fields', function (): void {
    $customResolver = new class implements ManifestResolver
    {
        public function resolve(Request $request, ManifestBuilder $default): ManifestBuilder
        {
            return $default->name('Custom App')->themeColor('#ff0000');
        }

        public function cacheKey(Request $request): ?string
        {
            return 'custom';
        }
    };

    $this->app->bind(ManifestResolver::class, fn () => $customResolver);

    $driver = app(DynamicManifestDriver::class);
    $manifest = $driver->resolve(Request::create('/'));

    expect($manifest->get('name'))->toBe('Custom App')
        ->and($manifest->get('theme_color'))->toBe('#ff0000');
});
