<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use SlFomin\PwaLaravel\Inertia\InertiaAdapter;
use SlFomin\PwaLaravel\Inertia\InertiaDetector;
use SlFomin\PwaLaravel\Inertia\InertiaPwaMiddleware;

// --- InertiaDetector ---

it('InertiaDetector::installed() returns true when Inertia is present', function (): void {
    expect(InertiaDetector::installed())->toBeTrue();
});

it('InertiaDetector::isInertiaRequest() detects X-Inertia header', function (): void {
    $request = Request::create('/', 'GET');
    $request->headers->set('X-Inertia', 'true');

    expect(InertiaDetector::isInertiaRequest($request))->toBeTrue();
});

it('InertiaDetector::isInertiaRequest() returns false without header', function (): void {
    $request = Request::create('/', 'GET');

    expect(InertiaDetector::isInertiaRequest($request))->toBeFalse();
});

it('InertiaDetector::isSsr() detects X-Inertia-SSR header', function (): void {
    $request = Request::create('/', 'GET');
    $request->headers->set('X-Inertia-SSR', '1');

    expect(InertiaDetector::isSsr($request))->toBeTrue();
});

it('InertiaDetector::isSsr() returns false without header', function (): void {
    $request = Request::create('/', 'GET');

    expect(InertiaDetector::isSsr($request))->toBeFalse();
});

// --- InertiaAdapter ---

it('shares pwa props through Inertia when enabled', function (): void {
    config()->set('pwa.inertia.share_props', true);

    Inertia::flushShared();
    app()->make(InertiaAdapter::class)->boot();

    $shared = value(Inertia::getShared('pwa'));

    expect($shared)->not->toBeNull()
        ->and($shared)->toHaveKey('manifest_url')
        ->and($shared)->toHaveKey('sw')
        ->and($shared)->toHaveKey('navigate_fallback')
        ->and($shared)->toHaveKey('is_ssr');
});

it('sw props contain expected fields', function (): void {
    config()->set('pwa.inertia.share_props', true);

    Inertia::flushShared();
    app()->make(InertiaAdapter::class)->boot();

    $sw = value(Inertia::getShared('pwa'))['sw'];

    expect($sw)->toHaveKey('url')
        ->and($sw)->toHaveKey('scope')
        ->and($sw)->toHaveKey('register_type')
        ->and($sw)->toHaveKey('auto_register')
        ->and($sw)->toHaveKey('available');
});

it('does not share when share_props=false', function (): void {
    config()->set('pwa.inertia.share_props', false);

    Inertia::flushShared();
    app()->make(InertiaAdapter::class)->boot();

    expect(Inertia::getShared('pwa'))->toBeNull();
});

it('uses custom shared_prop_key from config', function (): void {
    config()->set('pwa.inertia.share_props', true);
    config()->set('pwa.inertia.shared_prop_key', 'my_pwa');

    Inertia::flushShared();
    app()->make(InertiaAdapter::class)->boot();

    expect(Inertia::getShared('my_pwa'))->not->toBeNull();
    expect(Inertia::getShared('pwa'))->toBeNull();
});

it('manifest_url matches manifest driver url', function (): void {
    config()->set('pwa.inertia.share_props', true);
    config()->set('pwa.manifest.route', '/app.webmanifest');

    Inertia::flushShared();
    app()->make(InertiaAdapter::class)->boot();

    $shared = value(Inertia::getShared('pwa'));

    expect($shared['manifest_url'])->toBe('/app.webmanifest');
});

// --- InertiaPwaMiddleware ---

it('adds Vary and X-PWA-Inertia headers for Inertia requests', function (): void {
    $middleware = new InertiaPwaMiddleware;
    $request = Request::create('/', 'GET');
    $request->headers->set('X-Inertia', 'true');

    $response = $middleware->handle($request, fn () => new Response('ok'));

    expect($response->headers->get('Vary'))->toContain('X-Inertia')
        ->and($response->headers->get('X-PWA-Inertia'))->toBe('1');
});

it('adds no-store to Cache-Control for Inertia requests', function (): void {
    $middleware = new InertiaPwaMiddleware;
    $request = Request::create('/', 'GET');
    $request->headers->set('X-Inertia', 'true');

    $response = $middleware->handle($request, fn () => new Response('ok'));

    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

it('does not modify headers for regular requests', function (): void {
    $middleware = new InertiaPwaMiddleware;
    $request = Request::create('/', 'GET');

    $response = $middleware->handle($request, fn () => new Response('ok'));

    expect($response->headers->get('X-PWA-Inertia'))->toBeNull();
});

it('does not duplicate no-store if already present', function (): void {
    $middleware = new InertiaPwaMiddleware;
    $request = Request::create('/', 'GET');
    $request->headers->set('X-Inertia', 'true');

    $innerResponse = new Response('ok');
    $innerResponse->headers->set('Cache-Control', 'no-cache, no-store');

    $response = $middleware->handle($request, fn () => $innerResponse);
    $cacheControl = $response->headers->get('Cache-Control');

    expect(substr_count((string) $cacheControl, 'no-store'))->toBe(1);
});

// --- ServiceProvider registration ---

it('pwa.inertia middleware alias is registered', function (): void {
    $router = app()->make('router');

    expect($router->getMiddleware())->toHaveKey('pwa.inertia');
});

it('InertiaAdapter boots automatically when auto_detect=true', function (): void {
    // Auto-detect уже сработал в packageBooted()
    $shared = value(Inertia::getShared('pwa'));

    expect($shared)->not->toBeNull();
});
