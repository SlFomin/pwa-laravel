<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Shortcuts;

use ReflectionClass;
use SlFomin\PwaLaravel\Attributes\PwaIconSet;
use SlFomin\PwaLaravel\Core\Shortcuts\IconSetRegistry;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;
use SlFomin\PwaLaravel\Exceptions\AmbiguousIconSetException;
use SlFomin\PwaLaravel\Exceptions\IconSetNotFoundException;

/**
 * Reads icon sets from `PwaIconSet` class attributes on the given context class.
 *
 * Class context is required for this registry — there's no global notion of
 * attribute-declared sets, only per-class. Calling `get()` without a context
 * class always throws.
 */
final class AttributeIconSetRegistry implements IconSetRegistry
{
    /** @var array<class-string, array<string, list<ShortcutIcon>>> */
    private array $cache = [];

    public function get(string $name, ?string $contextClass = null): array
    {
        if ($contextClass === null) {
            throw new IconSetNotFoundException(
                "Icon set '{$name}': attribute-based lookup requires a context class."
            );
        }

        $sets = $this->loadForClass($contextClass);

        if (! isset($sets[$name])) {
            throw new IconSetNotFoundException(
                "Icon set '{$name}' is not declared via PwaIconSet on {$contextClass}."
            );
        }

        return $sets[$name];
    }

    public function has(string $name, ?string $contextClass = null): bool
    {
        if ($contextClass === null) {
            return false;
        }

        return isset($this->loadForClass($contextClass)[$name]);
    }

    public function all(): array
    {
        $merged = [];
        foreach ($this->cache as $sets) {
            $merged += $sets;
        }

        return $merged;
    }

    /** @return array<string, list<ShortcutIcon>> */
    private function loadForClass(string $class): array
    {
        if (isset($this->cache[$class])) {
            return $this->cache[$class];
        }

        if (! class_exists($class)) {
            return [];
        }

        $reflection = new ReflectionClass($class);
        $sets = [];

        foreach ($reflection->getAttributes(PwaIconSet::class) as $attr) {
            $instance = $attr->newInstance();
            if (isset($sets[$instance->name])) {
                throw new AmbiguousIconSetException(
                    "Icon set '{$instance->name}' declared multiple times on {$class}."
                );
            }
            $sets[$instance->name] = $instance->icons;
        }

        return $this->cache[$class] = $sets;
    }
}
