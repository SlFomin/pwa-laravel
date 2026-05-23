<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Shortcuts;

use Illuminate\Routing\Route;
use Illuminate\Routing\RouteCollectionInterface;
use ReflectionMethod;
use SlFomin\PwaLaravel\Attributes\PwaShortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\IconResolutionRequest;
use SlFomin\PwaLaravel\Core\Shortcuts\IconResolver;
use SlFomin\PwaLaravel\Core\Shortcuts\Shortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutCollection;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutDiscoverer;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

/**
 * Discovers shortcuts by scanning controller method attributes across all
 * registered routes.
 *
 * Iterates the application's route collection, reflects each controller method,
 * collects {@see PwaShortcut} attributes, and delegates icon resolution to an
 * {@see IconResolver}. Closure-based routes and routes with unresolvable
 * controllers are silently skipped (route-level errors are Laravel's concern,
 * not ours).
 *
 * This implementation is uncached — wrap it in {@see CachedDiscoverer} for
 * production use.
 */
final class RouteAttributeDiscoverer implements ShortcutDiscoverer
{
    public function __construct(
        private readonly RouteCollectionInterface $routes,
        private readonly IconResolver $iconResolver,
    ) {}

    public function discover(): ShortcutCollection
    {
        $shortcuts = [];

        foreach ($this->routes->getRoutes() as $route) {
            foreach ($this->discoverForRoute($route) as $shortcut) {
                $shortcuts[] = $shortcut;
            }
        }

        return new ShortcutCollection($shortcuts);
    }

    /** @return iterable<Shortcut> */
    private function discoverForRoute(Route $route): iterable
    {
        $action = $route->getAction('uses');

        if (! is_string($action) || ! str_contains($action, '@')) {
            return;
        }

        [$controllerClass, $methodName] = explode('@', $action, 2);

        if (! class_exists($controllerClass)) {
            return;
        }

        try {
            $reflection = new ReflectionMethod($controllerClass, $methodName);
        } catch (\ReflectionException) {
            return;
        }

        foreach ($reflection->getAttributes(PwaShortcut::class) as $attribute) {
            /** @var PwaShortcut $instance */
            $instance = $attribute->newInstance();
            yield $this->buildShortcut($instance, $route, $controllerClass);
        }
    }

    private function buildShortcut(
        PwaShortcut $attr,
        Route $route,
        string $controllerClass,
    ): Shortcut {
        $request = new IconResolutionRequest(
            iconString: is_string($attr->icon) ? $attr->icon : null,
            iconObject: $attr->icon instanceof ShortcutIcon
                ? $attr->icon
                : null,
            iconsArray: $attr->icons,
            iconSetName: $attr->iconSet,
            sizesHint: $attr->sizes,
            typeHint: $attr->type,
            sourceClass: $controllerClass,
        );

        $icons = $this->iconResolver->resolve($request);

        return new Shortcut(
            name: $attr->name,
            url: '/'.ltrim($route->uri(), '/'),
            icons: $icons,
            order: $attr->order,
        );
    }
}
