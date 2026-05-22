<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Laravel\Console;

use Illuminate\Console\Command;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutDiscoverer;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;
use SlFomin\PwaLaravel\Laravel\Shortcuts\RouteAttributeDiscoverer;

final class ShortcutsListCommand extends Command
{
    protected $signature = 'pwa:shortcuts:list {--no-cache : Bypass cache and re-scan}';

    protected $description = 'List all discovered PWA shortcuts';

    public function handle(): int
    {
        $discoverer = $this->option('no-cache')
            ? $this->laravel->make(RouteAttributeDiscoverer::class)
            : $this->laravel->make(ShortcutDiscoverer::class);

        $shortcuts = $discoverer->discover();

        if ($shortcuts->isEmpty()) {
            $this->warn('No PWA shortcuts discovered.');

            return self::SUCCESS;
        }

        $rows = [];
        $warnings = [];

        foreach ($shortcuts as $shortcut) {
            $iconsCell = $this->formatIcons($shortcut->icons, $warnings, $shortcut->name);
            $rows[] = [
                $shortcut->order,
                $shortcut->name,
                $shortcut->url,
                $iconsCell,
            ];
        }

        $this->table(['Order', 'Name', 'URL', 'Icons'], $rows);

        foreach ($warnings as $warning) {
            $this->warn('⚠  '.$warning);
        }

        return self::SUCCESS;
    }

    /**
     * @param  list<ShortcutIcon>  $icons
     * @param  list<string>  $warnings  Передаётся по ссылке для накопления предупреждений.
     */
    private function formatIcons(array $icons, array &$warnings, string $name): string
    {
        if ($icons === []) {
            $warnings[] = "'{$name}': no icon defined — will fall back to app icon.";

            return '(none)';
        }

        $lines = [];
        foreach ($icons as $icon) {
            $sizes = $icon->sizes ?? '(unknown)';
            $purpose = $icon->purpose !== null ? " [{$icon->purpose}]" : '';
            $lines[] = "{$icon->src} ({$sizes}){$purpose}";

            if ($icon->sizes === null) {
                $warnings[] = "'{$name}': icon {$icon->src} has unknown size — could not auto-probe.";
            }
        }

        return implode("\n", $lines);
    }
}
