<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use SlFomin\PwaLaravel\Events\IconsGenerated;
use SlFomin\PwaLaravel\Events\ManifestPublished;
use SlFomin\PwaLaravel\Events\ManifestResolved;
use SlFomin\PwaLaravel\Events\ManifestResolving;
use SlFomin\PwaLaravel\Events\PwaEvents;
use SlFomin\PwaLaravel\Events\ServiceWorkerRequested;
use SlFomin\PwaLaravel\PwaManager;

beforeEach(function (): void {
    config([
        'pwa.manifest.driver' => 'static',
        'pwa.manifest.static_path' => '/non/existent.webmanifest',
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

// --- ManifestResolving / ManifestResolved ---

it('dispatches ManifestResolving and ManifestResolved when PwaManager::manifest is called', function (): void {
    Event::fake([ManifestResolving::class, ManifestResolved::class]);

    app(PwaManager::class)->manifest();

    Event::assertDispatched(ManifestResolving::class);
    Event::assertDispatched(ManifestResolved::class, function (ManifestResolved $event): bool {
        return $event->manifest->get('name') === 'Test PWA';
    });
});

it('dispatches ManifestResolved when manifest route is requested', function (): void {
    Event::fake([ManifestResolved::class]);

    $this->get('/manifest.webmanifest')->assertOk();

    Event::assertDispatched(ManifestResolved::class);
});

it('allows ManifestResolved listener to mutate the manifest builder', function (): void {
    PwaEvents::manifestResolved(function (ManifestResolved $event): void {
        $event->manifest->name('Mutated Name')->themeColor('#ff0000');
    });

    $response = $this->get('/manifest.webmanifest')->assertOk();
    $data = $response->json();

    expect($data['name'])->toBe('Mutated Name')
        ->and($data['theme_color'])->toBe('#ff0000');
});

// --- ServiceWorkerRequested ---

it('dispatches ServiceWorkerRequested when SW route is served', function (): void {
    config(['pwa.service_worker.url' => '/sw.js']);
    $swPath = public_path('sw.js');
    file_put_contents($swPath, 'self.skipWaiting();');

    try {
        Event::fake([ServiceWorkerRequested::class]);

        $this->get('/sw.js')->assertOk();

        Event::assertDispatched(ServiceWorkerRequested::class, function (ServiceWorkerRequested $event) use ($swPath): bool {
            return $event->url === '/sw.js' && $event->path === $swPath;
        });
    } finally {
        @unlink($swPath);
    }
});

it('does not dispatch ServiceWorkerRequested when SW file is missing', function (): void {
    config(['pwa.service_worker.url' => '/sw.js']);

    Event::fake([ServiceWorkerRequested::class]);

    $this->get('/sw.js')->assertStatus(404);

    Event::assertNotDispatched(ServiceWorkerRequested::class);
});

// --- ManifestPublished ---

it('dispatches ManifestPublished after pwa:publish-manifest succeeds', function (): void {
    $tmpPath = sys_get_temp_dir().'/pwa-events-'.uniqid().'.webmanifest';
    config(['pwa.manifest.static_path' => $tmpPath]);

    try {
        Event::fake([ManifestPublished::class]);

        $this->artisan('pwa:publish-manifest')->assertExitCode(0);

        Event::assertDispatched(ManifestPublished::class, function (ManifestPublished $event) use ($tmpPath): bool {
            return $event->path === $tmpPath
                && $event->bytes > 0
                && $event->manifest->get('name') === 'Test PWA';
        });
    } finally {
        @unlink($tmpPath);
    }
});

// --- IconsGenerated ---

it('dispatches IconsGenerated after pwa:generate-icons succeeds', function (): void {
    $source = __DIR__.'/../fixtures/icon-source-512.png';
    $output = sys_get_temp_dir().'/pwa-icons-events-'.uniqid();

    if (! file_exists($source)) {
        $this->markTestSkipped('Source icon fixture missing.');
    }

    try {
        Event::fake([IconsGenerated::class]);

        $this->artisan('pwa:generate-icons', ['source' => $source, '--output' => $output])
            ->assertExitCode(0);

        Event::assertDispatched(IconsGenerated::class, function (IconsGenerated $event) use ($source, $output): bool {
            return $event->sourcePath === $source
                && $event->outputPath === $output
                && count($event->icons) > 0;
        });
    } finally {
        if (is_dir($output)) {
            foreach (glob($output.'/*') ?: [] as $f) {
                @unlink($f);
            }
            @rmdir($output);
        }
    }
});

// --- PwaEvents helper ---

it('PwaEvents::manifestResolving registers a listener', function (): void {
    $called = false;
    PwaEvents::manifestResolving(function (ManifestResolving $event) use (&$called): void {
        $called = true;
    });

    app(PwaManager::class)->manifest();

    expect($called)->toBeTrue();
});

it('PwaEvents helpers register listeners for all five events', function (): void {
    $dispatcher = Event::getFacadeRoot();

    PwaEvents::manifestResolving(fn () => null);
    PwaEvents::manifestResolved(fn () => null);
    PwaEvents::serviceWorkerRequested(fn () => null);
    PwaEvents::iconsGenerated(fn () => null);
    PwaEvents::manifestPublished(fn () => null);

    expect($dispatcher->hasListeners(ManifestResolving::class))->toBeTrue()
        ->and($dispatcher->hasListeners(ManifestResolved::class))->toBeTrue()
        ->and($dispatcher->hasListeners(ServiceWorkerRequested::class))->toBeTrue()
        ->and($dispatcher->hasListeners(IconsGenerated::class))->toBeTrue()
        ->and($dispatcher->hasListeners(ManifestPublished::class))->toBeTrue();
});
