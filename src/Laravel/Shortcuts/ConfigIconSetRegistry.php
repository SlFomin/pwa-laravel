<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Laravel\Shortcuts;

use Illuminate\Contracts\Config\Repository as Config;
use SlFomin\PwaLaravel\Core\Shortcuts\IconSetRegistry;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;
use SlFomin\PwaLaravel\Laravel\Exceptions\IconSetNotFoundException;

/**
 * Reads icon sets from Laravel's config repository under `pwa.icon_sets`.
 *
 * Expected config shape:
 *
 *     'icon_sets' => [
 *         'login' => [
 *             ['src' => '/icons/login-96.png', 'sizes' => '96x96'],
 *             ['src' => '/icons/login.svg', 'sizes' => 'any', 'type' => 'image/svg+xml'],
 *         ],
 *     ],
 *
 * Each entry must contain at least `src`. Optional keys: `sizes`, `type`, `purpose`.
 */
final class ConfigIconSetRegistry implements IconSetRegistry
{
    public function __construct(
        private readonly Config $config,
        private readonly string $configKey = 'pwa.icon_sets',
    ) {}

    public function get(string $name, ?string $contextClass = null): array
    {
        $raw = $this->config->get("{$this->configKey}.{$name}");

        if (! is_array($raw)) {
            throw new IconSetNotFoundException(
                "Icon set '{$name}' is not defined in config('{$this->configKey}')."
            );
        }

        return array_values(array_map(
            fn ($entry) => match (true) {
                $entry instanceof ShortcutIcon => $entry,
                is_array($entry) => new ShortcutIcon(
                    src: $entry['src'],
                    sizes: $entry['sizes'] ?? null,
                    type: $entry['type'] ?? null,
                    purpose: $entry['purpose'] ?? null,
                ),
                default => throw new \InvalidArgumentException(
                    "Icon set '{$name}': entry must be ShortcutIcon or array."
                ),
            },
            $raw,
        ));
    }

    public function has(string $name, ?string $contextClass = null): bool
    {
        return is_array($this->config->get("{$this->configKey}.{$name}"));
    }

    public function all(): array
    {
        $sets = $this->config->get($this->configKey, []);
        $result = [];
        foreach ($sets as $name => $entries) {
            $result[$name] = $this->get($name);
        }

        return $result;
    }
}
