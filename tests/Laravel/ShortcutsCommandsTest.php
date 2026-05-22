<?php

declare(strict_types=1);

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use SlFomin\PwaLaravel\Core\Shortcuts\Shortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutCollection;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutDiscoverer;
use SlFomin\PwaLaravel\Laravel\Shortcuts\CachedDiscoverer;

function bindFakeDiscoverer(array $shortcuts): void
{
    app()->bind(ShortcutDiscoverer::class, fn () => new class ($shortcuts) implements ShortcutDiscoverer
    {
        public function __construct(private readonly array $items) {}

        public function discover(): ShortcutCollection
        {
            return new ShortcutCollection($this->items);
        }
    });
}

// ─── pwa:shortcuts:list ───────────────────────────────────────────────────────

it('pwa:shortcuts:list shows table when shortcuts exist', function (): void {
    bindFakeDiscoverer([
        new Shortcut('Login', '/login', order: 10),
        new Shortcut('Register', '/register', order: 20),
    ]);

    $this->artisan('pwa:shortcuts:list')
        ->assertSuccessful()
        ->expectsOutputToContain('Login');
});

it('pwa:shortcuts:list warns when no shortcuts are found', function (): void {
    bindFakeDiscoverer([]);

    $this->artisan('pwa:shortcuts:list')
        ->assertSuccessful()
        ->expectsOutputToContain('No PWA shortcuts discovered');
});

it('pwa:shortcuts:list --no-cache uses RouteAttributeDiscoverer directly', function (): void {
    // RouteAttributeDiscoverer scans real routes; no #[PwaShortcut] routes exist in tests
    $this->artisan('pwa:shortcuts:list', ['--no-cache' => true])
        ->assertSuccessful();
});

it('pwa:shortcuts:list warns about shortcuts with unknown icon size', function (): void {
    bindFakeDiscoverer([
        new Shortcut('Login', '/login', [
            new SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon('/icon.png'),
        ]),
    ]);

    $this->artisan('pwa:shortcuts:list')
        ->assertSuccessful()
        ->expectsOutputToContain('unknown size');
});

// ─── pwa:shortcuts:clear ─────────────────────────────────────────────────────

it('pwa:shortcuts:clear reports nothing-to-clear when caching is disabled', function (): void {
    config(['pwa.shortcuts.cache_enabled' => false]);

    $this->artisan('pwa:shortcuts:clear')
        ->assertSuccessful()
        ->expectsOutputToContain('nothing to clear');
});

it('pwa:shortcuts:clear flushes cache and reports success when CachedDiscoverer is active', function (): void {
    config(['pwa.shortcuts.cache_enabled' => true, 'cache.default' => 'array']);

    // Pre-populate the cache with something
    $discoverer = app(ShortcutDiscoverer::class);
    expect($discoverer)->toBeInstanceOf(CachedDiscoverer::class);

    $this->artisan('pwa:shortcuts:clear')
        ->assertSuccessful()
        ->expectsOutputToContain('Cleared');
});

// ─── pwa:shortcuts:cache ─────────────────────────────────────────────────────

it('pwa:shortcuts:cache warns when caching is disabled', function (): void {
    config(['pwa.shortcuts.cache_enabled' => false]);

    $this->artisan('pwa:shortcuts:cache')
        ->assertSuccessful()
        ->expectsOutputToContain('disabled');
});

it('pwa:shortcuts:cache reports cached count when CachedDiscoverer is active', function (): void {
    config(['pwa.shortcuts.cache_enabled' => true, 'cache.default' => 'array']);

    $this->artisan('pwa:shortcuts:cache')
        ->assertSuccessful()
        ->expectsOutputToContain('Cached');
});
