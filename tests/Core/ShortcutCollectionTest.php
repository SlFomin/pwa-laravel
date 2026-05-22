<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Core\Shortcuts\Shortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutCollection;

it('is empty by default', function (): void {
    $c = new ShortcutCollection();

    expect($c->isEmpty())->toBeTrue()
        ->and($c->count())->toBe(0);
});

it('is not empty when given shortcuts', function (): void {
    $c = new ShortcutCollection([new Shortcut('A', '/a')]);

    expect($c->isEmpty())->toBeFalse()
        ->and($c->count())->toBe(1);
});

it('sorts shortcuts by order ascending', function (): void {
    $c = new ShortcutCollection([
        new Shortcut('C', '/c', order: 30),
        new Shortcut('A', '/a', order: 10),
        new Shortcut('B', '/b', order: 20),
    ]);

    $names = array_map(fn (Shortcut $s) => $s->name, iterator_to_array($c));

    expect($names)->toBe(['A', 'B', 'C']);
});

it('is iterable via foreach', function (): void {
    $c = new ShortcutCollection([new Shortcut('X', '/x')]);
    $seen = [];

    foreach ($c as $s) {
        $seen[] = $s->name;
    }

    expect($seen)->toBe(['X']);
});

it('accepts a Generator', function (): void {
    $gen = function (): \Generator {
        yield new Shortcut('X', '/x');
        yield new Shortcut('Y', '/y');
    };

    $c = new ShortcutCollection($gen());

    expect($c->count())->toBe(2);
});

it('toManifestArray returns all shortcuts serialized in order', function (): void {
    $c = new ShortcutCollection([
        new Shortcut('B', '/b', order: 20),
        new Shortcut('A', '/a', order: 10),
    ]);

    $arr = $c->toManifestArray();

    expect($arr)->toHaveCount(2)
        ->and($arr[0]['name'])->toBe('A')
        ->and($arr[1]['name'])->toBe('B');
});

it('toManifestArray returns empty array when empty', function (): void {
    expect((new ShortcutCollection())->toManifestArray())->toBe([]);
});
