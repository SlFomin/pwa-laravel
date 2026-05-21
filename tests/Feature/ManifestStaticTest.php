<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SlFomin\PwaLaravel\Manifest\Drivers\StaticManifestDriver;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

beforeEach(function (): void {
    config([
        'pwa.manifest.driver' => 'static',
        'pwa.manifest.route' => '/manifest.webmanifest',
        'pwa.manifest.data' => [
            'name' => 'Test PWA',
            'short_name' => 'Test',
            'start_url' => '/',
            'display' => 'standalone',
            'theme_color' => '#000000',
            'background_color' => '#ffffff',
        ],
    ]);
});

it('returns manifest from config when static file does not exist', function (): void {
    config(['pwa.manifest.static_path' => '/non/existent/manifest.webmanifest']);

    $driver = new StaticManifestDriver;
    $manifest = $driver->resolve(Request::create('/'));

    expect($manifest)->toBeInstanceOf(ManifestBuilder::class)
        ->and($manifest->get('name'))->toBe('Test PWA');
});

it('returns manifest from file when it exists', function (): void {
    $tmpFile = tempnam(sys_get_temp_dir(), 'pwa_test_');
    file_put_contents($tmpFile, json_encode([
        'name' => 'From File',
        'short_name' => 'File',
        'start_url' => '/',
        'display' => 'standalone',
    ]));

    config(['pwa.manifest.static_path' => $tmpFile]);

    $driver = new StaticManifestDriver;
    $manifest = $driver->resolve(Request::create('/'));

    expect($manifest->get('name'))->toBe('From File');

    unlink($tmpFile);
});

it('falls back to config when file contains invalid json', function (): void {
    $tmpFile = tempnam(sys_get_temp_dir(), 'pwa_test_');
    file_put_contents($tmpFile, 'not valid json {{{');

    config(['pwa.manifest.static_path' => $tmpFile]);

    $driver = new StaticManifestDriver;
    $manifest = $driver->resolve(Request::create('/'));

    expect($manifest->get('name'))->toBe('Test PWA');

    unlink($tmpFile);
});

it('logs warning when manifest file contains invalid json', function (): void {
    Log::spy();

    $tmpFile = tempnam(sys_get_temp_dir(), 'pwa_test_');
    file_put_contents($tmpFile, 'not valid json {{{');
    config(['pwa.manifest.static_path' => $tmpFile]);

    (new StaticManifestDriver)->resolve(Request::create('/'));

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(fn (string $message) => str_contains($message, '[PWA]'));

    unlink($tmpFile);
});

it('returns correct manifest url', function (): void {
    $driver = new StaticManifestDriver;
    $url = $driver->url(Request::create('/'));

    expect($url)->toBe('/manifest.webmanifest');
});

it('returns correct link attributes for static driver', function (): void {
    $driver = new StaticManifestDriver;
    $attrs = $driver->linkAttributes(Request::create('/'));

    expect($attrs)->toMatchArray([
        'rel' => 'manifest',
        'href' => '/manifest.webmanifest',
    ])->not->toHaveKey('crossorigin');
});

it('manifest route responds 200 in static mode', function (): void {
    config(['pwa.manifest.static_path' => '/non/existent.webmanifest']);

    $response = $this->get('/manifest.webmanifest');
    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/manifest+json');
});

it('manifest route returns valid json', function (): void {
    config(['pwa.manifest.static_path' => '/non/existent.webmanifest']);

    $response = $this->get('/manifest.webmanifest');
    $data = $response->json();

    expect($data)->toBeArray()
        ->and($data['name'])->toBe('Test PWA')
        ->and($data['display'])->toBe('standalone');
});

it('manifest route is named pwa.manifest', function (): void {
    expect(route('pwa.manifest'))->toEndWith('/manifest.webmanifest');
});

it('manifest route does not use web middleware', function (): void {
    $route = app('router')->getRoutes()->getByName('pwa.manifest');
    expect($route)->not->toBeNull()
        ->and($route->gatherMiddleware())->not->toContain('web');
});
