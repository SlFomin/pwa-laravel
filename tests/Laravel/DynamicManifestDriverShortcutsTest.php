<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Core\Shortcuts\Shortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutCollection;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutDiscoverer;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;
use SlFomin\PwaLaravel\Manifest\Drivers\DynamicManifestDriver;
use SlFomin\PwaLaravel\Manifest\Resolvers\DefaultManifestResolver;

function bindShortcuts(array $shortcuts): void
{
    app()->bind(ShortcutDiscoverer::class, fn () => new class($shortcuts) implements ShortcutDiscoverer
    {
        public function __construct(private readonly array $items) {}

        public function discover(): ShortcutCollection
        {
            return new ShortcutCollection($this->items);
        }
    });
}

beforeEach(function (): void {
    config([
        'pwa.shortcuts.enabled' => true,
        'pwa.manifest.driver' => 'dynamic',
        'pwa.manifest.data' => [
            'name' => 'App',
            'short_name' => 'App',
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

it('injects discovered shortcuts when manifest.data.shortcuts is empty', function (): void {
    bindShortcuts([new Shortcut('Login', '/login')]);

    $driver = app(DynamicManifestDriver::class);
    $manifest = $driver->resolve(Request::create('/'));

    expect($manifest->get('shortcuts'))->toHaveCount(1)
        ->and($manifest->get('shortcuts')[0]['name'])->toBe('Login');
});

it('does not override non-empty manifest.data.shortcuts from config', function (): void {
    config(['pwa.manifest.data.shortcuts' => [['name' => 'Static', 'url' => '/static']]]);

    bindShortcuts([new Shortcut('Discovered', '/discovered')]);

    $driver = app(DynamicManifestDriver::class);
    $manifest = $driver->resolve(Request::create('/'));

    expect($manifest->get('shortcuts'))->toHaveCount(1)
        ->and($manifest->get('shortcuts')[0]['name'])->toBe('Static');
});

it('skips injection when shortcuts.enabled is false', function (): void {
    config(['pwa.shortcuts.enabled' => false]);

    bindShortcuts([new Shortcut('Login', '/login')]);

    $driver = app(DynamicManifestDriver::class);
    $manifest = $driver->resolve(Request::create('/'));

    expect($manifest->get('shortcuts', []))->toBe([]);
});

it('skips injection when discoverer returns empty collection', function (): void {
    bindShortcuts([]);

    $driver = app(DynamicManifestDriver::class);
    $manifest = $driver->resolve(Request::create('/'));

    // Empty collection → no shortcuts key written
    expect($manifest->get('shortcuts', []))->toBe([]);
});

it('includes shortcut icons in manifest output', function (): void {
    bindShortcuts([
        new Shortcut('Login', '/login', [
            new ShortcutIcon('/icons/login.png', '192x192', 'image/png'),
        ]),
    ]);

    $driver = app(DynamicManifestDriver::class);
    $manifest = $driver->resolve(Request::create('/'));

    $icons = $manifest->get('shortcuts')[0]['icons'];

    expect($icons)->toHaveCount(1)
        ->and($icons[0]['src'])->toBe('/icons/login.png')
        ->and($icons[0]['sizes'])->toBe('192x192');
});

it('shortcuts appear in JSON output of the manifest', function (): void {
    bindShortcuts([new Shortcut('Dashboard', '/dashboard')]);

    $driver = app(DynamicManifestDriver::class);
    $manifest = $driver->resolve(Request::create('/'));

    $data = json_decode($manifest->toJson(), true);

    expect($data['shortcuts'][0]['name'])->toBe('Dashboard')
        ->and($data['shortcuts'][0]['url'])->toBe('/dashboard');
});

it('shortcuts are injected inside cache closure and cached', function (): void {
    config(['pwa.manifest.dynamic.cache' => true, 'cache.default' => 'array']);

    bindShortcuts([new Shortcut('Login', '/login')]);

    $driver = app(DynamicManifestDriver::class);

    $first = $driver->resolve(Request::create('/'));
    $second = $driver->resolve(Request::create('/'));

    expect($first->get('shortcuts'))->toHaveCount(1)
        ->and($second->get('shortcuts'))->toHaveCount(1);
});
