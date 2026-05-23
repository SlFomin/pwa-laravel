<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Laravel\Console;

use Illuminate\Console\Command;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutDiscoverer;
use SlFomin\PwaLaravel\Laravel\Shortcuts\CachedDiscoverer;

final class ShortcutsClearCommand extends Command
{
    protected $signature = 'pwa:shortcuts:clear';

    protected $description = 'Clear the cached PWA shortcuts';

    public function handle(): int
    {
        /** @var ShortcutDiscoverer $discoverer */
        $discoverer = $this->laravel->make(ShortcutDiscoverer::class);

        if (! $discoverer instanceof CachedDiscoverer) {
            $this->info('Shortcuts caching is disabled — nothing to clear.');

            return self::SUCCESS;
        }

        $discoverer->flush();
        $this->info('Cleared PWA shortcuts cache.');

        return self::SUCCESS;
    }
}
