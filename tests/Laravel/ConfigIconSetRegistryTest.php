<?php

declare(strict_types=1);

use Illuminate\Config\Repository;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;
use SlFomin\PwaLaravel\Laravel\Exceptions\IconSetNotFoundException;
use SlFomin\PwaLaravel\Laravel\Shortcuts\ConfigIconSetRegistry;

function configRepo(array $data): Repository
{
    return new Repository($data);
}

// --- get() ---

it('resolves icons from array entries', function (): void {
    $registry = new ConfigIconSetRegistry(configRepo(['pwa' => ['icon_sets' => [
        'auth' => [
            ['src' => '/icons/auth-96.png', 'sizes' => '96x96', 'type' => 'image/png'],
        ],
    ]]]));

    $icons = $registry->get('auth');

    expect($icons)->toHaveCount(1)
        ->and($icons[0])->toBeInstanceOf(ShortcutIcon::class)
        ->and($icons[0]->src)->toBe('/icons/auth-96.png')
        ->and($icons[0]->sizes)->toBe('96x96')
        ->and($icons[0]->type)->toBe('image/png');
});

it('returns ShortcutIcon objects from config unchanged', function (): void {
    $icon = new ShortcutIcon('/icons/auth.svg', 'any', 'image/svg+xml');
    $registry = new ConfigIconSetRegistry(configRepo(['pwa' => ['icon_sets' => [
        'auth' => [$icon],
    ]]]));

    $icons = $registry->get('auth');

    expect($icons[0])->toBe($icon);
});

it('fills optional fields with null when absent from array entry', function (): void {
    $registry = new ConfigIconSetRegistry(configRepo(['pwa' => ['icon_sets' => [
        'minimal' => [['src' => '/icon.png']],
    ]]]));

    $icon = $registry->get('minimal')[0];

    expect($icon->sizes)->toBeNull()
        ->and($icon->type)->toBeNull()
        ->and($icon->purpose)->toBeNull();
});

it('throws IconSetNotFoundException for missing set', function (): void {
    $registry = new ConfigIconSetRegistry(configRepo(['pwa' => ['icon_sets' => []]]));

    $registry->get('nonexistent');
})->throws(IconSetNotFoundException::class);

it('throws InvalidArgumentException for invalid entry type', function (): void {
    $registry = new ConfigIconSetRegistry(configRepo(['pwa' => ['icon_sets' => [
        'bad' => ['not-an-array-or-icon-object'],
    ]]]));

    $registry->get('bad');
})->throws(InvalidArgumentException::class);

// --- has() ---

it('has() returns true for an existing set', function (): void {
    $registry = new ConfigIconSetRegistry(configRepo(['pwa' => ['icon_sets' => [
        'auth' => [['src' => '/icon.png']],
    ]]]));

    expect($registry->has('auth'))->toBeTrue();
});

it('has() returns false for a missing set', function (): void {
    $registry = new ConfigIconSetRegistry(configRepo(['pwa' => ['icon_sets' => []]]));

    expect($registry->has('nonexistent'))->toBeFalse();
});

it('has() ignores contextClass parameter', function (): void {
    $registry = new ConfigIconSetRegistry(configRepo(['pwa' => ['icon_sets' => [
        'auth' => [['src' => '/icon.png']],
    ]]]));

    expect($registry->has('auth', 'App\\Http\\Controllers\\AuthController'))->toBeTrue();
});

// --- all() ---

it('all() returns all defined sets', function (): void {
    $registry = new ConfigIconSetRegistry(configRepo(['pwa' => ['icon_sets' => [
        'auth' => [['src' => '/icons/auth-96.png', 'sizes' => '96x96']],
        'admin' => [['src' => '/icons/admin-96.png', 'sizes' => '96x96']],
    ]]]));

    $all = $registry->all();

    expect($all)->toHaveKeys(['auth', 'admin'])
        ->and($all['auth'])->toHaveCount(1)
        ->and($all['admin'])->toHaveCount(1);
});

it('all() returns empty array when no sets configured', function (): void {
    $registry = new ConfigIconSetRegistry(configRepo(['pwa' => ['icon_sets' => []]]));

    expect($registry->all())->toBe([]);
});
