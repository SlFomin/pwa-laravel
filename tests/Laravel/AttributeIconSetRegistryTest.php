<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Laravel\Exceptions\AmbiguousIconSetException;
use SlFomin\PwaLaravel\Laravel\Exceptions\IconSetNotFoundException;
use SlFomin\PwaLaravel\Laravel\Shortcuts\AttributeIconSetRegistry;
use SlFomin\PwaLaravel\Tests\Fixtures\AmbiguousIconSetController;
use SlFomin\PwaLaravel\Tests\Fixtures\IconSetTestController;

// --- get() ---

it('returns icons for a set declared on the context class', function (): void {
    $registry = new AttributeIconSetRegistry;

    $icons = $registry->get('auth', IconSetTestController::class);

    expect($icons)->toHaveCount(2)
        ->and($icons[0]->src)->toBe('/icons/auth-96.png')
        ->and($icons[1]->src)->toBe('/icons/auth-192.png');
});

it('returns different set icons by name', function (): void {
    $registry = new AttributeIconSetRegistry;

    $icons = $registry->get('admin', IconSetTestController::class);

    expect($icons)->toHaveCount(1)
        ->and($icons[0]->src)->toBe('/icons/admin-96.png');
});

it('throws IconSetNotFoundException when contextClass is null', function (): void {
    $registry = new AttributeIconSetRegistry;

    $registry->get('auth');
})->throws(IconSetNotFoundException::class, 'requires a context class');

it('throws IconSetNotFoundException when set not found on class', function (): void {
    $registry = new AttributeIconSetRegistry;

    $registry->get('nonexistent', IconSetTestController::class);
})->throws(IconSetNotFoundException::class);

it('throws AmbiguousIconSetException for duplicate set name on same class', function (): void {
    $registry = new AttributeIconSetRegistry;

    $registry->get('auth', AmbiguousIconSetController::class);
})->throws(AmbiguousIconSetException::class);

// --- has() ---

it('has() returns false without context class', function (): void {
    $registry = new AttributeIconSetRegistry;

    expect($registry->has('auth'))->toBeFalse();
});

it('has() returns true when set exists on context class', function (): void {
    $registry = new AttributeIconSetRegistry;

    expect($registry->has('auth', IconSetTestController::class))->toBeTrue();
});

it('has() returns false when set does not exist on context class', function (): void {
    $registry = new AttributeIconSetRegistry;

    expect($registry->has('nonexistent', IconSetTestController::class))->toBeFalse();
});

it('has() returns false for a non-existent class', function (): void {
    $registry = new AttributeIconSetRegistry;

    expect($registry->has('auth', 'App\\NonExistentController'))->toBeFalse();
});

// --- all() ---

it('all() returns empty array before any class is loaded', function (): void {
    $registry = new AttributeIconSetRegistry;

    expect($registry->all())->toBe([]);
});

it('all() aggregates sets from all cached classes', function (): void {
    $registry = new AttributeIconSetRegistry;

    $registry->get('auth', IconSetTestController::class);

    $all = $registry->all();

    expect($all)->toHaveKeys(['auth', 'admin']);
});
