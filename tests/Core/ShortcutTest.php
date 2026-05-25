<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Core\Shortcuts\Shortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

it('constructs with required fields only', function (): void {
    $s = new Shortcut('Login', '/login');

    expect($s->name)->toBe('Login')
        ->and($s->url)->toBe('/login')
        ->and($s->icons)->toBe([])
        ->and($s->order)->toBe(100);
});

it('constructs with all fields', function (): void {
    $icon = new ShortcutIcon('/icon.png', '192x192');
    $s = new Shortcut('Login', '/login', [$icon], 10);

    expect($s->icons)->toHaveCount(1)
        ->and($s->order)->toBe(10);
});

it('toManifestArray includes name and url', function (): void {
    $s = new Shortcut('Login', '/login');

    expect($s->toManifestArray())->toBe(['name' => 'Login', 'url' => '/login']);
});

it('toManifestArray omits icons key when empty', function (): void {
    $s = new Shortcut('Login', '/login', []);

    expect($s->toManifestArray())->not->toHaveKey('icons');
});

it('toManifestArray includes serialized icons when present', function (): void {
    $icon = new ShortcutIcon('/icon.png', '192x192', 'image/png');
    $s = new Shortcut('Login', '/login', [$icon]);

    $arr = $s->toManifestArray();

    expect($arr)->toHaveKey('icons')
        ->and($arr['icons'])->toHaveCount(1)
        ->and($arr['icons'][0]['src'])->toBe('/icon.png')
        ->and($arr['icons'][0]['sizes'])->toBe('192x192');
});

it('toManifestArray serializes multiple icons', function (): void {
    $s = new Shortcut('Register', '/register', [
        new ShortcutIcon('/a.png', '96x96'),
        new ShortcutIcon('/b.png', '192x192'),
    ]);

    expect($s->toManifestArray()['icons'])->toHaveCount(2);
});
