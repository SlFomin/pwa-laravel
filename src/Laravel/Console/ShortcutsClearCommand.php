<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Laravel\Console;

use Illuminate\Console\Command;
use SlFomin\PwaLaravel\Laravel\Shortcuts\CachedDiscoverer;

final class ShortcutsClearCommand extends Command
{
    protected $signature = 'pwa:shortcuts:clear';

    protected $description = 'Clear the cached PWA shortcuts';

    public function handle(): int
    {
        $this->laravel->make(CachedDiscoverer::class)->flush();
        $this->info('Cleared PWA shortcuts cache.');

        return self::SUCCESS;
    }
}
