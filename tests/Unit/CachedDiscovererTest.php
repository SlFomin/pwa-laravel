<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Core\Shortcuts\Shortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutCollection;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutDiscoverer;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;
use SlFomin\PwaLaravel\Shortcuts\CachedDiscoverer;

function makeInnerDiscoverer(array $shortcuts, ?int &$callCount = null): ShortcutDiscoverer
{
    return new class($shortcuts, $callCount) implements ShortcutDiscoverer
    {
        public function __construct(
            private readonly array $shortcuts,
            private ?int &$callCount,
        ) {}

        public function discover(): ShortcutCollection
        {
            if ($this->callCount !== null) {
                $this->callCount++;
            }

            return new ShortcutCollection($this->shortcuts);
        }
    };
}

beforeEach(function (): void {
    config(['cache.default' => 'array']);
});

it('calls inner discoverer on first call (cache miss)', function (): void {
    $calls = 0;
    $inner = makeInnerDiscoverer([new Shortcut('Login', '/login')], $calls);
    $cached = new CachedDiscoverer($inner, app('cache')->store());

    $collection = $cached->discover();

    expect($collection->count())->toBe(1)
        ->and($calls)->toBe(1);
});

it('returns cached result on second call (cache hit)', function (): void {
    $calls = 0;
    $inner = makeInnerDiscoverer([new Shortcut('Login', '/login')], $calls);
    $cached = new CachedDiscoverer($inner, app('cache')->store());

    $cached->discover();
    $cached->discover();

    expect($calls)->toBe(1);
});

it('hydrates shortcuts correctly from cache', function (): void {
    $original = new Shortcut('Login', '/login', [
        new ShortcutIcon('/icon.png', '192x192', 'image/png', 'any'),
    ], order: 5);
    $inner = makeInnerDiscoverer([$original]);
    $cached = new CachedDiscoverer($inner, app('cache')->store());

    // Populate cache
    $cached->discover();

    // Read from cache (second call)
    $result = iterator_to_array($cached->discover());

    expect($result[0]->name)->toBe('Login')
        ->and($result[0]->url)->toBe('/login')
        ->and($result[0]->order)->toBe(5)
        ->and($result[0]->icons[0]->src)->toBe('/icon.png')
        ->and($result[0]->icons[0]->sizes)->toBe('192x192')
        ->and($result[0]->icons[0]->purpose)->toBe('any');
});

it('flush() forces re-discovery on next call', function (): void {
    $calls = 0;
    $inner = makeInnerDiscoverer([new Shortcut('Login', '/login')], $calls);
    $cached = new CachedDiscoverer($inner, app('cache')->store());

    $cached->discover();
    $cached->flush();
    $cached->discover();

    expect($calls)->toBe(2);
});

it('uses a custom cache key when provided', function (): void {
    $inner = makeInnerDiscoverer([new Shortcut('X', '/x')]);
    $cached = new CachedDiscoverer($inner, app('cache')->store(), 'my.custom.key');

    $cached->discover();

    expect(app('cache')->store()->has('my.custom.key'))->toBeTrue();
});

it('returns empty collection when inner returns nothing', function (): void {
    $inner = makeInnerDiscoverer([]);
    $cached = new CachedDiscoverer($inner, app('cache')->store());

    expect($cached->discover()->isEmpty())->toBeTrue();
});
