<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Core\Shortcuts\IconSetRegistry;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

function bindFakeIconSetRegistry(array $sets): void
{
    app()->bind(IconSetRegistry::class, fn () => new class($sets) implements IconSetRegistry
    {
        public function __construct(private readonly array $sets) {}

        public function get(string $name, ?string $contextClass = null): array
        {
            return $this->sets[$name] ?? [];
        }

        public function has(string $name, ?string $contextClass = null): bool
        {
            return isset($this->sets[$name]);
        }

        public function all(): array
        {
            return $this->sets;
        }
    });
}

// --- pwa:icon-sets:list ---

it('pwa:icon-sets:list shows set name and icon src for registered sets', function (): void {
    bindFakeIconSetRegistry([
        // Use a set name that is not a substring of the icon src to avoid
        // Mockery intercepting both expectsOutputToContain with the same doWrite call.
        'login' => [new ShortcutIcon('/icons/shield.png', '96x96')],
    ]);

    $this->artisan('pwa:icon-sets:list')
        ->assertSuccessful()
        ->expectsOutputToContain('login')
        ->expectsOutputToContain('/icons/shield.png');
});

it('pwa:icon-sets:list warns when no sets are registered', function (): void {
    bindFakeIconSetRegistry([]);

    $this->artisan('pwa:icon-sets:list')
        ->assertSuccessful()
        ->expectsOutputToContain('No icon sets registered');
});
