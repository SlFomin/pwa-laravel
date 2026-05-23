<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Laravel\Console;

use Illuminate\Console\Command;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutDiscoverer;
use SlFomin\PwaLaravel\Laravel\Shortcuts\CachedDiscoverer;

final class ShortcutsCacheCommand extends Command
{
    protected $signature = 'pwa:shortcuts:cache';

    protected $description = 'Discover PWA shortcuts and cache the result for production';

    public function handle(): int
    {
        /** @var ShortcutDiscoverer $discoverer */
        $discoverer = $this->laravel->make(ShortcutDiscoverer::class);

        if (! $discoverer instanceof CachedDiscoverer) {
            $this->warn('Shortcuts caching is disabled (pwa.shortcuts.cache_enabled=false).');
            $collection = $discoverer->discover();
            $this->info(sprintf('Discovered %d shortcut(s) (not cached).', count($collection)));

            return self::SUCCESS;
        }

        $discoverer->flush();
        $collection = $discoverer->discover();
        $this->info(sprintf('Cached %d shortcut(s).', count($collection)));

        return self::SUCCESS;
    }
}
