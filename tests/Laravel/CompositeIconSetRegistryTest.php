<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Core\Shortcuts\IconSetRegistry;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;
use SlFomin\PwaLaravel\Laravel\Exceptions\IconSetNotFoundException;
use SlFomin\PwaLaravel\Laravel\Shortcuts\CompositeIconSetRegistry;

function makeFixedIconSetRegistry(array $sets): IconSetRegistry
{
    return new class($sets) implements IconSetRegistry
    {
        public function __construct(private readonly array $sets) {}

        public function get(string $name, ?string $contextClass = null): array
        {
            if (! isset($this->sets[$name])) {
                throw new IconSetNotFoundException("Not found: {$name}");
            }

            return $this->sets[$name];
        }

        public function has(string $name, ?string $contextClass = null): bool
        {
            return isset($this->sets[$name]);
        }

        public function all(): array
        {
            return $this->sets;
        }
    };
}

// --- get() ---

it('returns icons from the first registry when it has the set', function (): void {
    $icon1 = new ShortcutIcon('/icons/from-first.png', '96x96');
    $icon2 = new ShortcutIcon('/icons/from-second.png', '192x192');

    $composite = new CompositeIconSetRegistry([
        makeFixedIconSetRegistry(['auth' => [$icon1]]),
        makeFixedIconSetRegistry(['auth' => [$icon2]]),
    ]);

    $icons = $composite->get('auth');

    expect($icons[0]->src)->toBe('/icons/from-first.png');
});

it('falls through to second registry when first does not have the set', function (): void {
    $icon = new ShortcutIcon('/icons/from-config.png', '96x96');

    $composite = new CompositeIconSetRegistry([
        makeFixedIconSetRegistry([]),
        makeFixedIconSetRegistry(['auth' => [$icon]]),
    ]);

    $icons = $composite->get('auth');

    expect($icons[0]->src)->toBe('/icons/from-config.png');
});

it('throws IconSetNotFoundException when set is not in any registry', function (): void {
    $composite = new CompositeIconSetRegistry([
        makeFixedIconSetRegistry([]),
        makeFixedIconSetRegistry([]),
    ]);

    $composite->get('nonexistent');
})->throws(IconSetNotFoundException::class);

it('includes contextClass in the not-found error message', function (): void {
    $composite = new CompositeIconSetRegistry([
        makeFixedIconSetRegistry([]),
    ]);

    $composite->get('auth', 'App\\Http\\Controllers\\AuthController');
})->throws(IconSetNotFoundException::class, 'App\\Http\\Controllers\\AuthController');

// --- has() ---

it('has() returns true when any registry has the set', function (): void {
    $composite = new CompositeIconSetRegistry([
        makeFixedIconSetRegistry([]),
        makeFixedIconSetRegistry(['auth' => [new ShortcutIcon('/i.png')]]),
    ]);

    expect($composite->has('auth'))->toBeTrue();
});

it('has() returns false when no registry has the set', function (): void {
    $composite = new CompositeIconSetRegistry([
        makeFixedIconSetRegistry([]),
        makeFixedIconSetRegistry([]),
    ]);

    expect($composite->has('nonexistent'))->toBeFalse();
});

// --- all() ---

it('all() merges sets from all registries with first-registry winning on conflict', function (): void {
    $iconFirst = new ShortcutIcon('/icons/from-first.png', '96x96');
    $iconSecond = new ShortcutIcon('/icons/from-second.png', '192x192');

    $composite = new CompositeIconSetRegistry([
        makeFixedIconSetRegistry(['auth' => [$iconFirst]]),
        makeFixedIconSetRegistry(['auth' => [$iconSecond], 'admin' => [new ShortcutIcon('/admin.png')]]),
    ]);

    $all = $composite->all();

    expect($all)->toHaveKeys(['auth', 'admin'])
        ->and($all['auth'][0]->src)->toBe('/icons/from-first.png');
});

it('all() returns empty array when all registries are empty', function (): void {
    $composite = new CompositeIconSetRegistry([
        makeFixedIconSetRegistry([]),
        makeFixedIconSetRegistry([]),
    ]);

    expect($composite->all())->toBe([]);
});
