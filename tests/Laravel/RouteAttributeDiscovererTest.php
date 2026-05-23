<?php

declare(strict_types=1);

use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollection;
use SlFomin\PwaLaravel\Core\Shortcuts\IconMetadata;
use SlFomin\PwaLaravel\Core\Shortcuts\IconMetadataProbe;
use SlFomin\PwaLaravel\Core\Shortcuts\IconResolver;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutCollection;
use SlFomin\PwaLaravel\Laravel\Shortcuts\RouteAttributeDiscoverer;
use SlFomin\PwaLaravel\Tests\Fixtures\IconSetTestController;
use SlFomin\PwaLaravel\Tests\Fixtures\ShortcutTestController;

beforeEach(function (): void {
    // Null probe — no filesystem reads in unit tests
    $this->app->bind(IconMetadataProbe::class, fn () => new class implements IconMetadataProbe
    {
        public function probe(string $src): ?IconMetadata
        {
            return null;
        }
    });
});

function makeRoutes(array $definitions): RouteCollection
{
    $collection = new RouteCollection;
    foreach ($definitions as [$uri, $action]) {
        $route = new Route(['GET'], $uri, ['uses' => $action]);
        $collection->add($route);
    }

    return $collection;
}

// --- Basic discovery ---

it('discovers a shortcut from a controller method with #[PwaShortcut]', function (): void {
    $routes = makeRoutes([['/login', ShortcutTestController::class.'@showLogin']]);
    $discoverer = new RouteAttributeDiscoverer($routes, app(IconResolver::class));
    $collection = $discoverer->discover();

    expect($collection)->toBeInstanceOf(ShortcutCollection::class)
        ->and($collection->count())->toBe(1);

    $shortcuts = iterator_to_array($collection);
    expect($shortcuts[0]->name)->toBe('Login')
        ->and($shortcuts[0]->url)->toBe('/login');
});

it('discovers multiple shortcuts from a single method (repeatable attribute)', function (): void {
    $routes = makeRoutes([['/register', ShortcutTestController::class.'@showRegister']]);
    $discoverer = new RouteAttributeDiscoverer($routes, app(IconResolver::class));
    $collection = $discoverer->discover();

    expect($collection->count())->toBe(2);
});

it('sorts discovered shortcuts by order', function (): void {
    $routes = makeRoutes([
        ['/login', ShortcutTestController::class.'@showLogin'],
        ['/register', ShortcutTestController::class.'@showRegister'],
    ]);
    $discoverer = new RouteAttributeDiscoverer($routes, app(IconResolver::class));

    $names = array_map(
        fn ($s) => $s->name,
        iterator_to_array($discoverer->discover())
    );

    expect($names[0])->toBe('Sign Up'); // order 5
});

it('derives URL from the route URI', function (): void {
    $routes = makeRoutes([['/dashboard', ShortcutTestController::class.'@dashboard']]);
    $discoverer = new RouteAttributeDiscoverer($routes, app(IconResolver::class));
    $shortcuts = iterator_to_array($discoverer->discover());

    expect($shortcuts[0]->url)->toBe('/dashboard');
});

it('skips routes with no #[PwaShortcut] attribute', function (): void {
    $routes = makeRoutes([['/no-attr', ShortcutTestController::class.'@noShortcut']]);
    $discoverer = new RouteAttributeDiscoverer($routes, app(IconResolver::class));

    expect($discoverer->discover()->isEmpty())->toBeTrue();
});

// --- Edge cases ---

it('skips closure-based routes silently', function (): void {
    $collection = new RouteCollection;
    $route = new Route(['GET'], '/home', ['uses' => fn () => 'home']);
    $collection->add($route);

    $discoverer = new RouteAttributeDiscoverer($collection, app(IconResolver::class));

    expect($discoverer->discover()->isEmpty())->toBeTrue();
});

it('skips routes with non-existent controller class silently', function (): void {
    $routes = makeRoutes([['/ghost', 'App\\Http\\Controllers\\GhostController@index']]);
    $discoverer = new RouteAttributeDiscoverer($routes, app(IconResolver::class));

    expect($discoverer->discover()->isEmpty())->toBeTrue();
});

it('returns empty collection when route collection is empty', function (): void {
    $discoverer = new RouteAttributeDiscoverer(new RouteCollection, app(IconResolver::class));

    expect($discoverer->discover()->isEmpty())->toBeTrue();
});

it('resolves icons from explicit ShortcutIcon objects in attribute', function (): void {
    $routes = makeRoutes([['/dashboard', ShortcutTestController::class.'@dashboard']]);
    $discoverer = new RouteAttributeDiscoverer($routes, app(IconResolver::class));
    $shortcuts = iterator_to_array($discoverer->discover());

    expect($shortcuts[0]->icons)->toHaveCount(2)
        ->and($shortcuts[0]->icons[0]->src)->toBe('/icons/dash-96.png')
        ->and($shortcuts[0]->icons[1]->src)->toBe('/icons/dash-192.png');
});

it('resolves shortcut icons via iconSet from class PwaIconSet attribute', function (): void {
    $routes = makeRoutes([['/login', IconSetTestController::class.'@showLogin']]);
    $discoverer = new RouteAttributeDiscoverer($routes, app(IconResolver::class));
    $shortcuts = iterator_to_array($discoverer->discover());

    expect($shortcuts[0]->name)->toBe('Login')
        ->and($shortcuts[0]->icons)->toHaveCount(2)
        ->and($shortcuts[0]->icons[0]->src)->toBe('/icons/auth-96.png')
        ->and($shortcuts[0]->icons[1]->src)->toBe('/icons/auth-192.png');
});
