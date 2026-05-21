<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Contracts\ServiceWorkerStrategy;
use SlFomin\PwaLaravel\ServiceWorker\Strategies\GenerateSWStrategy;
use SlFomin\PwaLaravel\ServiceWorker\Strategies\InjectManifestStrategy;
use SlFomin\PwaLaravel\ServiceWorker\WorkerManager;

// --- WorkerManager ---

it('WorkerManager returns configured url', function (): void {
    config(['pwa.service_worker.url' => '/my-sw.js']);

    $manager = app(WorkerManager::class);
    expect($manager->registrationUrl())->toBe('/my-sw.js');
});

it('WorkerManager returns default url when not configured', function (): void {
    $manager = app(WorkerManager::class);
    expect($manager->registrationUrl())->toBe('/sw.js');
});

it('WorkerManager returns configured scope', function (): void {
    config(['pwa.service_worker.scope' => '/app/']);

    $manager = app(WorkerManager::class);
    expect($manager->scope())->toBe('/app/');
});

it('WorkerManager isAutoRegister returns true by default', function (): void {
    $manager = app(WorkerManager::class);
    expect($manager->isAutoRegister())->toBeTrue();
});

it('WorkerManager isAutoRegister respects config', function (): void {
    config(['pwa.service_worker.auto_register' => false]);

    $manager = app(WorkerManager::class);
    expect($manager->isAutoRegister())->toBeFalse();
});

it('WorkerManager shouldRegisterInDev returns false by default', function (): void {
    $manager = app(WorkerManager::class);
    expect($manager->shouldRegisterInDev())->toBeFalse();
});

it('WorkerManager registrationScript contains sw url and scope', function (): void {
    config([
        'pwa.service_worker.url' => '/sw.js',
        'pwa.service_worker.scope' => '/',
    ]);

    $script = app(WorkerManager::class)->registrationScript();
    expect($script)->toContain("'/sw.js'")
        ->and($script)->toContain("scope: '/'");
});

it('WorkerManager registrationScript contains SKIP_WAITING for autoUpdate', function (): void {
    config(['pwa.service_worker.register_type' => 'autoUpdate']);

    $script = app(WorkerManager::class)->registrationScript();
    expect($script)->toContain('SKIP_WAITING');
});

// --- Strategies ---

it('GenerateSWStrategy returns correct url from config', function (): void {
    config(['pwa.service_worker.url' => '/my-sw.js']);

    $strategy = new GenerateSWStrategy;
    expect($strategy->url())->toBe('/my-sw.js');
});

it('GenerateSWStrategy viteOptions has generateSW strategy', function (): void {
    $strategy = new GenerateSWStrategy;
    $opts = $strategy->viteOptions();
    expect($opts['strategies'])->toBe('generateSW');
});

it('GenerateSWStrategy viteOptions filename strips leading slash', function (): void {
    config(['pwa.service_worker.url' => '/sw.js']);

    $strategy = new GenerateSWStrategy;
    expect($strategy->viteOptions()['filename'])->toBe('sw.js');
});

it('InjectManifestStrategy viteOptions has injectManifest strategy', function (): void {
    $strategy = new InjectManifestStrategy;
    $opts = $strategy->viteOptions();
    expect($opts['strategies'])->toBe('injectManifest');
});

it('InjectManifestStrategy viteOptions contains srcDir', function (): void {
    $strategy = new InjectManifestStrategy;
    expect($strategy->viteOptions())->toHaveKey('srcDir');
});

it('ServiceWorkerStrategy binding resolves GenerateSW by default', function (): void {
    $strategy = app(ServiceWorkerStrategy::class);
    expect($strategy)->toBeInstanceOf(GenerateSWStrategy::class);
});

it('ServiceWorkerStrategy binding resolves InjectManifest when configured', function (): void {
    config(['pwa.service_worker.strategy' => 'injectManifest']);

    $strategy = app(ServiceWorkerStrategy::class);
    expect($strategy)->toBeInstanceOf(InjectManifestStrategy::class);
});

// --- ServiceWorkerController ---

it('GET /sw.js returns 404 when file does not exist', function (): void {
    config(['pwa.service_worker.url' => '/sw.js']);

    $response = $this->get('/sw.js');
    $response->assertStatus(404);
});

it('GET /sw.js returns 200 with JS content type when file exists', function (): void {
    config(['pwa.service_worker.url' => '/sw.js']);

    $swPath = public_path('sw.js');
    file_put_contents($swPath, "self.addEventListener('fetch', function(){});");

    try {
        $response = $this->get('/sw.js');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/javascript; charset=utf-8');
    } finally {
        unlink($swPath);
    }
});

it('GET /sw.js response includes Service-Worker-Allowed header', function (): void {
    config([
        'pwa.service_worker.url' => '/sw.js',
        'pwa.service_worker.scope' => '/',
    ]);

    $swPath = public_path('sw.js');
    file_put_contents($swPath, 'self.skipWaiting();');

    try {
        $response = $this->get('/sw.js');
        $response->assertHeader('Service-Worker-Allowed', '/');
    } finally {
        unlink($swPath);
    }
});

// --- PwaHeaders middleware ---

it('PwaHeaders adds service-worker headers to sw url', function (): void {
    config([
        'pwa.service_worker.url' => '/sw.js',
        'pwa.headers.service_worker' => ['Service-Worker-Allowed' => '/'],
    ]);

    $swPath = public_path('sw.js');
    file_put_contents($swPath, 'self.skipWaiting();');

    try {
        $response = $this->get('/sw.js');
        $response->assertHeader('Service-Worker-Allowed', '/');
    } finally {
        unlink($swPath);
    }
});

it('PwaHeaders adds manifest headers to manifest url', function (): void {
    config([
        'pwa.manifest.route' => '/manifest.webmanifest',
        'pwa.headers.manifest' => ['Cache-Control' => 'no-store'],
        'pwa.manifest.driver' => 'static',
        'pwa.manifest.static_path' => null,
    ]);

    $response = $this->get('/manifest.webmanifest');
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

it('PwaHeaders does not add sw headers to other paths', function (): void {
    config([
        'pwa.service_worker.url' => '/sw.js',
        'pwa.headers.service_worker' => ['X-SW-Custom' => 'yes'],
    ]);

    $response = $this->get('/');
    expect($response->headers->has('X-SW-Custom'))->toBeFalse();
});
