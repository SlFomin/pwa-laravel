<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Shortcuts;

use SlFomin\PwaLaravel\Core\Shortcuts\IconSetRegistry;
use SlFomin\PwaLaravel\Exceptions\IconSetNotFoundException;

/**
 * Composes multiple registries in priority order. Attribute-based lookup
 * (class-local) takes precedence over config-based (global).
 */
final class CompositeIconSetRegistry implements IconSetRegistry
{
    /** @param list<IconSetRegistry> $registries Ordered by priority, first wins. */
    public function __construct(
        private readonly array $registries,
    ) {}

    public function get(string $name, ?string $contextClass = null): array
    {
        foreach ($this->registries as $registry) {
            if ($registry->has($name, $contextClass)) {
                return $registry->get($name, $contextClass);
            }
        }

        throw new IconSetNotFoundException(
            sprintf(
                "Icon set '%s' not found%s.",
                $name,
                $contextClass !== null ? " (context: {$contextClass})" : '',
            ),
        );
    }

    public function has(string $name, ?string $contextClass = null): bool
    {
        return array_any($this->registries, fn ($registry) => $registry->has($name, $contextClass));
    }

    public function all(): array
    {
        $merged = [];
        foreach (array_reverse($this->registries) as $registry) {
            $merged = array_merge($merged, $registry->all());
        }

        return $merged;
    }
}
