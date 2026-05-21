<?php

declare(strict_types=1);
use SlFomin\PwaLaravel\Contracts\ManifestDriver;
use SlFomin\PwaLaravel\ServiceWorker\WorkerManager;

it('renders pwa meta directive', function (): void {
    config([
        'pwa.manifest.driver' => 'static',
        'pwa.manifest.static_path' => '/non/existent',
        'pwa.manifest.route' => '/manifest.webmanifest',
        'pwa.manifest.data' => [
            'name' => 'My PWA',
            'short_name' => 'PWA',
            'start_url' => '/',
            'display' => 'standalone',
            'theme_color' => '#123456',
        ],
        'pwa.icons.output_url_prefix' => '/icons',
        'pwa.icons.favicon_sizes' => [16, 32],
    ]);

    $html = (string) view()->make('pwa::directives.meta', [
        'manifest' => app(ManifestDriver::class)->resolve(request()),
        'manifestLink' => app(ManifestDriver::class)->linkAttributes(request()),
    ]);

    expect($html)
        ->toContain('rel="manifest"')
        ->toContain('href="/manifest.webmanifest"')
        ->toContain('content="#123456"')
        ->toContain('apple-mobile-web-app-capable');
});

it('renders pwa sw-register directive when auto_register enabled', function (): void {
    config([
        'pwa.service_worker.auto_register' => true,
        'pwa.service_worker.dev_enabled' => true,
        'pwa.service_worker.url' => '/sw.js',
        'pwa.service_worker.scope' => '/',
        'pwa.service_worker.register_type' => 'autoUpdate',
    ]);

    $html = (string) view()->make('pwa::directives.sw-register', [
        'worker' => app(WorkerManager::class),
    ]);

    expect($html)
        ->toContain('serviceWorker')
        ->toContain('navigator.serviceWorker.register');
});

it('pwa sw-register is empty when auto_register disabled', function (): void {
    config([
        'pwa.service_worker.auto_register' => false,
        'pwa.service_worker.dev_enabled' => false,
    ]);

    $html = trim((string) view()->make('pwa::directives.sw-register', [
        'worker' => app(WorkerManager::class),
    ]));

    expect($html)->toBeEmpty();
});

it('renders install button with default text', function (): void {
    $html = (string) view()->make('pwa::directives.install-button', [
        'text' => 'Install app',
    ]);

    expect($html)
        ->toContain('pwa-install-button')
        ->toContain('Install app')
        ->toContain('beforeinstallprompt');
});
