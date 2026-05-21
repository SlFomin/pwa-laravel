<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\ServiceWorker\ViteManifestBridge;

beforeEach(function (): void {
    $this->fixturePath = __DIR__.'/../fixtures/vite-manifest.json';
});

it('returns empty array when manifest file does not exist', function (): void {
    config(['pwa.vite.manifest_path' => '/non/existent/path.json']);

    $bridge = new ViteManifestBridge;
    expect($bridge->load())->toBeEmpty();
});

it('loads manifest from file', function (): void {
    config(['pwa.vite.manifest_path' => $this->fixturePath]);

    $bridge = new ViteManifestBridge;
    $manifest = $bridge->load();

    expect($manifest)->toBeArray()
        ->and($manifest)->toHaveKey('resources/js/app.js');
});

it('resolves asset url', function (): void {
    config([
        'pwa.vite.manifest_path' => $this->fixturePath,
        'pwa.vite.base_url' => '/build/',
    ]);

    $bridge = new ViteManifestBridge;
    $url = $bridge->asset('resources/js/app.js');

    expect($url)->toBe('/build/assets/app-BqPH2cDD.js');
});

it('returns null for unknown asset', function (): void {
    config(['pwa.vite.manifest_path' => $this->fixturePath]);

    $bridge = new ViteManifestBridge;
    expect($bridge->asset('unknown/entry.js'))->toBeNull();
});

it('caches loaded manifest', function (): void {
    config(['pwa.vite.manifest_path' => $this->fixturePath]);

    $bridge = new ViteManifestBridge;
    $first = $bridge->load();
    $second = $bridge->load();

    expect($first)->toBe($second);
});

it('clears cache', function (): void {
    config(['pwa.vite.manifest_path' => $this->fixturePath]);

    $bridge = new ViteManifestBridge;
    $bridge->load();
    $bridge->clear();

    $property = new ReflectionProperty($bridge, 'manifest');
    $property->setAccessible(true);
    expect($property->getValue($bridge))->toBeNull();
});

it('exists returns false when file missing', function (): void {
    config(['pwa.vite.manifest_path' => '/no/such/file.json']);

    $bridge = new ViteManifestBridge;
    expect($bridge->exists())->toBeFalse();
});

it('exists returns true when file present', function (): void {
    config(['pwa.vite.manifest_path' => $this->fixturePath]);

    $bridge = new ViteManifestBridge;
    expect($bridge->exists())->toBeTrue();
});
