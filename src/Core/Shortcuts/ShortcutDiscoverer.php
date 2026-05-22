<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Core\Shortcuts;

/**
 * Discovers all available PWA shortcuts in the application.
 *
 * Implementations decide WHERE to look (route attributes, config files, manually
 * registered shortcuts, etc.). The contract guarantees an ordered, deduplicated
 * collection ready for inclusion in a manifest.
 *
 * Implementations should be idempotent — calling `discover()` multiple times
 * within a single request should return equivalent collections.
 */
interface ShortcutDiscoverer
{
    public function discover(): ShortcutCollection;
}
