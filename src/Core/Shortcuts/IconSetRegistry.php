<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Core\Shortcuts;

/**
 * Resolves named icon sets to concrete {@see ShortcutIcon} arrays.
 */
interface IconSetRegistry
{
    /**
     * @return list<ShortcutIcon>
     */
    public function get(string $name, ?string $contextClass = null): array;

    public function has(string $name, ?string $contextClass = null): bool;

    /** @return array<string, list<ShortcutIcon>> */
    public function all(): array;
}
