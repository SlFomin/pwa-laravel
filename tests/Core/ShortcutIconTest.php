<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

it('constructs with src only', function (): void {
    $icon = new ShortcutIcon('/icon.png');

    expect($icon->src)->toBe('/icon.png')
        ->and($icon->sizes)->toBeNull()
        ->and($icon->type)->toBeNull()
        ->and($icon->purpose)->toBeNull();
});

it('toArray contains only src when other fields are null', function (): void {
    expect((new ShortcutIcon('/icon.png'))->toArray())->toBe(['src' => '/icon.png']);
});

it('toArray includes all non-null fields', function (): void {
    $icon = new ShortcutIcon('/icon.png', '192x192', 'image/png', 'any');

    expect($icon->toArray())->toBe([
        'src' => '/icon.png',
        'sizes' => '192x192',
        'type' => 'image/png',
        'purpose' => 'any',
    ]);
});

it('toArray omits null sizes', function (): void {
    $icon = new ShortcutIcon('/icon.png', null, 'image/png');

    expect($icon->toArray())->not->toHaveKey('sizes')
        ->and($icon->toArray())->toHaveKey('type');
});

it('accepts valid single purpose tokens', function (string $purpose): void {
    $icon = new ShortcutIcon('/icon.png', purpose: $purpose);

    expect($icon->purpose)->toBe($purpose);
})->with(['any', 'maskable', 'monochrome']);

it('accepts multi-token purpose string', function (): void {
    $icon = new ShortcutIcon('/icon.png', purpose: 'any maskable');

    expect($icon->purpose)->toBe('any maskable');
});

it('rejects unknown purpose token', function (): void {
    new ShortcutIcon('/icon.png', purpose: 'invalid');
})->throws(InvalidArgumentException::class, "invalid purpose token 'invalid'");

it('rejects purpose with one valid and one invalid token', function (): void {
    new ShortcutIcon('/icon.png', purpose: 'any foobar');
})->throws(InvalidArgumentException::class);

it('null purpose does not throw', function (): void {
    $icon = new ShortcutIcon('/icon.png', purpose: null);

    expect($icon->purpose)->toBeNull();
});
