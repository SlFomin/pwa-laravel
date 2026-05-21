<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Exceptions\InvalidManifestException;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

it('creates builder from array', function (): void {
    $builder = ManifestBuilder::make([
        'name' => 'Test App',
        'short_name' => 'Test',
        'start_url' => '/',
        'display' => 'standalone',
    ]);

    expect($builder->toArray())->toMatchArray([
        'name' => 'Test App',
        'short_name' => 'Test',
    ]);
});

it('supports fluent api', function (): void {
    $builder = ManifestBuilder::make()
        ->name('My App')
        ->shortName('App')
        ->startUrl('/')
        ->display('standalone')
        ->themeColor('#ffffff')
        ->backgroundColor('#000000')
        ->lang('ru');

    expect($builder->data['name'])->toBe('My App')
        ->and($builder->data['short_name'])->toBe('App')
        ->and($builder->data['lang'])->toBe('ru');
});

it('adds icons correctly', function (): void {
    $builder = ManifestBuilder::make()
        ->name('App')->shortName('App')->startUrl('/')->display('standalone')
        ->addIcon('/icons/icon-192.png', '192x192', 'image/png', 'any')
        ->addIcon('/icons/icon-512.png', '512x512', 'image/png', 'maskable');

    $icons = $builder->data['icons'];
    expect($icons)->toHaveCount(2)
        ->and($icons[0]['purpose'])->toBe('any')
        ->and($icons[1]['purpose'])->toBe('maskable');
});

it('merges data', function (): void {
    $builder = ManifestBuilder::make(['name' => 'Old', 'short_name' => 'O', 'start_url' => '/', 'display' => 'standalone'])
        ->merge(['name' => 'New', 'lang' => 'en']);

    expect($builder->data['name'])->toBe('New')
        ->and($builder->data['lang'])->toBe('en');
});

it('serializes to json', function (): void {
    $json = ManifestBuilder::make([
        'name' => 'App',
        'short_name' => 'App',
        'start_url' => '/',
        'display' => 'standalone',
    ])->toJson();

    $decoded = json_decode($json, true);
    expect($decoded)->toBeArray()
        ->and($decoded['name'])->toBe('App');
});

it('validates required fields', function (): void {
    ManifestBuilder::make(['name' => 'App'])->validate();
})->throws(InvalidManifestException::class, "'short_name' is required");

it('rejects invalid display value', function (): void {
    ManifestBuilder::make([
        'name' => 'App',
        'short_name' => 'App',
        'start_url' => '/',
        'display' => 'invalid',
    ])->validate();
})->throws(InvalidManifestException::class, 'Invalid display value');

it('rejects invalid theme color', function (): void {
    ManifestBuilder::make([
        'name' => 'App',
        'short_name' => 'App',
        'start_url' => '/',
        'display' => 'standalone',
        'theme_color' => 'not-a-color-value!!!',
    ])->validate();
})->throws(InvalidManifestException::class, 'Invalid theme_color');

it('accepts valid color formats', function (string $color): void {
    $builder = ManifestBuilder::make([
        'name' => 'App',
        'short_name' => 'App',
        'start_url' => '/',
        'display' => 'standalone',
        'theme_color' => $color,
    ]);

    expect($builder->validate())->toBeInstanceOf(ManifestBuilder::class);
})->with(['#fff', '#ffffff', '#AABBCC', 'rgb(0,0,0)', 'rgba(0,0,0,1)', 'white']);

it('get returns default when key missing', function (): void {
    $builder = ManifestBuilder::make();
    expect($builder->get('missing', 'fallback'))->toBe('fallback');
});

it('implements json serializable', function (): void {
    $builder = ManifestBuilder::make([
        'name' => 'App',
        'short_name' => 'App',
        'start_url' => '/',
        'display' => 'standalone',
    ]);

    $json = json_encode($builder);
    expect($json)->toBeString()
        ->and(json_decode($json, true)['name'])->toBe('App');
});
