<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Laravel\Console;

use Illuminate\Console\Command;
use SlFomin\PwaLaravel\Core\Shortcuts\IconSetRegistry;

final class IconSetsListCommand extends Command
{
    protected $signature = 'pwa:icon-sets:list';

    protected $description = 'List all registered PWA icon sets';

    public function handle(IconSetRegistry $registry): int
    {
        $sets = $registry->all();

        if ($sets === []) {
            $this->warn('No icon sets registered.');

            return self::SUCCESS;
        }

        foreach ($sets as $name => $icons) {
            $this->line("<info>{$name}</info>:");
            foreach ($icons as $icon) {
                $this->line("  - {$icon->src} ({$icon->sizes})"
                    .($icon->purpose ? " [{$icon->purpose}]" : ''));
            }
        }

        return self::SUCCESS;
    }
}
