<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Shortcuts;

use Illuminate\Contracts\Cache\Repository as Cache;
use SlFomin\PwaLaravel\Core\Shortcuts\Shortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutCollection;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutDiscoverer;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

/**
 * Decorates any {@see ShortcutDiscoverer} with a serializable cache layer.
 *
 * Caches the serialized representation of the discovered collection. Cache
 * is populated lazily on first call; explicit refresh via {@see flush()} or
 * the `pwa:shortcuts:cache` Artisan command (typically in deploy pipelines).
 *
 * Cache key is versioned: bumping the constant forces cache invalidation
 * on package upgrades that change the serialized shape.
 */
final class CachedDiscoverer implements ShortcutDiscoverer
{
    public const string CACHE_KEY = 'pwa-laravel.shortcuts.v1';

    public function __construct(
        private readonly ShortcutDiscoverer $inner,
        private readonly Cache $cache,
        private readonly string $cacheKey = self::CACHE_KEY,
    ) {}

    public function discover(): ShortcutCollection
    {
        $cached = $this->cache->get($this->cacheKey);

        if ($this->isValidCachedShape($cached)) {
            return $this->hydrate($cached);
        }

        $fresh = $this->inner->discover();
        $this->cache->forever($this->cacheKey, $this->dehydrate($fresh));

        return $fresh;
    }

    /**
     * @phpstan-assert-if-true list<array<string, mixed>> $cached
     */
    private function isValidCachedShape(mixed $cached): bool
    {
        if (! is_array($cached) || ! array_is_list($cached)) {
            return false;
        }

        return array_all($cached, fn ($row) => is_array($row) && isset($row['name'], $row['url'], $row['icons']));
    }

    public function flush(): void
    {
        $this->cache->forget($this->cacheKey);
    }

    /** @return list<array<string, mixed>> */
    private function dehydrate(ShortcutCollection $collection): array
    {
        $result = [];
        foreach ($collection as $shortcut) {
            $result[] = [
                'name' => $shortcut->name,
                'url' => $shortcut->url,
                'order' => $shortcut->order,
                'icons' => array_map(
                    fn (ShortcutIcon $i) => [
                        'src' => $i->src,
                        'sizes' => $i->sizes,
                        'type' => $i->type,
                        'purpose' => $i->purpose,
                    ],
                    $shortcut->icons,
                ),
            ];
        }

        return $result;
    }

    /** @param list<array<string, mixed>> $data */
    private function hydrate(array $data): ShortcutCollection
    {
        $shortcuts = [];
        foreach ($data as $row) {
            $icons = [];
            foreach ($row['icons'] as $iconData) {
                $icons[] = new ShortcutIcon(
                    src: $iconData['src'],
                    sizes: $iconData['sizes'],
                    type: $iconData['type'],
                    purpose: $iconData['purpose'],
                );
            }
            $shortcuts[] = new Shortcut(
                name: $row['name'],
                url: $row['url'],
                icons: $icons,
                order: $row['order'],
            );
        }

        return new ShortcutCollection($shortcuts);
    }
}
