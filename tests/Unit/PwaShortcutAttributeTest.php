<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Attributes\PwaShortcut;
use SlFomin\PwaLaravel\Core\Exceptions\InvalidShortcutDefinitionException;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

it('constructs with name only', function (): void {
    $attr = new PwaShortcut('Login');

    expect($attr->name)->toBe('Login')
        ->and($attr->icon)->toBeNull()
        ->and($attr->icons)->toBeNull()
        ->and($attr->sizes)->toBeNull()
        ->and($attr->type)->toBeNull()
        ->and($attr->order)->toBe(100);
});

it('constructs with a string icon', function (): void {
    $attr = new PwaShortcut('Login', icon: '/icons/login.png');

    expect($attr->icon)->toBe('/icons/login.png');
});

it('constructs with a ShortcutIcon object', function (): void {
    $icon = new ShortcutIcon('/icons/login.png', '192x192');
    $attr = new PwaShortcut('Login', icon: $icon);

    expect($attr->icon)->toBe($icon);
});

it('constructs with an icons array', function (): void {
    $icons = [new ShortcutIcon('/a.png'), new ShortcutIcon('/b.png')];
    $attr = new PwaShortcut('Login', icons: $icons);

    expect($attr->icons)->toBe($icons);
});

it('allows sizes and type hints with a string icon', function (): void {
    $attr = new PwaShortcut('Login', icon: '/icon.png', sizes: '192x192', type: 'image/png');

    expect($attr->sizes)->toBe('192x192')
        ->and($attr->type)->toBe('image/png');
});

it('accepts a custom order', function (): void {
    $attr = new PwaShortcut('Login', order: 5);

    expect($attr->order)->toBe(5);
});

// --- Validation errors ---

it('throws when icon and icons are both provided', function (): void {
    new PwaShortcut('Login', icon: '/a.png', icons: [new ShortcutIcon('/b.png')]);
})->throws(InvalidShortcutDefinitionException::class, 'use only one of `icon`, `icons`, `iconSet`');

it('constructs with iconSet parameter', function (): void {
    $attr = new PwaShortcut('Login', iconSet: 'auth');

    expect($attr->iconSet)->toBe('auth')
        ->and($attr->icon)->toBeNull()
        ->and($attr->icons)->toBeNull();
});

it('throws when iconSet and icon are both provided', function (): void {
    new PwaShortcut('Login', icon: '/a.png', iconSet: 'auth');
})->throws(InvalidShortcutDefinitionException::class, 'use only one of `icon`, `icons`, `iconSet`');

it('throws when iconSet and icons are both provided', function (): void {
    new PwaShortcut('Login', icons: [new ShortcutIcon('/a.png')], iconSet: 'auth');
})->throws(InvalidShortcutDefinitionException::class, 'use only one of `icon`, `icons`, `iconSet`');

it('throws when sizes is used with a ShortcutIcon object', function (): void {
    new PwaShortcut('Login', icon: new ShortcutIcon('/icon.png'), sizes: '192x192');
})->throws(InvalidShortcutDefinitionException::class, '`sizes`/`type` parameters are only valid');

it('throws when type is used with an icons array', function (): void {
    new PwaShortcut('Login', icons: [new ShortcutIcon('/icon.png')], type: 'image/png');
})->throws(InvalidShortcutDefinitionException::class);

it('throws when sizes is used with no icon', function (): void {
    new PwaShortcut('Login', sizes: '192x192');
})->throws(InvalidShortcutDefinitionException::class);
