<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Core\Shortcuts;

/**
 * Represents a single PWA shortcut entry as defined by the W3C Web App Manifest specification.
 *
 * A shortcut is a quick action item displayed in the OS application context menu
 * (long-press on app icon on mobile, right-click on desktop). Each shortcut points
 * to a specific URL within the application's scope.
 *
 * This is a framework-agnostic core DTO. Construction (via attributes, route
 * discovery, config parsing) happens in the Laravel bridge layer and produces
 * instances of this class as output.
 *
 * @see https://www.w3.org/TR/manifest-app-info/#shortcuts-member  W3C: shortcuts member
 * @see https://www.w3.org/TR/manifest-app-info/#shortcutitem-and-its-members  W3C: ShortcutItem
 * @see https://developer.mozilla.org/en-US/docs/Web/Manifest/shortcuts  MDN: shortcuts
 */
final readonly class Shortcut
{
    /**
     * @param  string  $name
     *                        Human-readable label displayed in the shortcut menu. Required by spec.
     *                        Should be concise — typically 1-2 words. Browsers may truncate long names.
     * @param  string  $url
     *                       Target URL within the manifest's scope. Must be a same-origin path.
     * @param  list<ShortcutIcon>  $icons
     *                                     Zero or more icon variants. Empty array is valid — browsers fall back
     *                                     to the main application icon for this shortcut. Providing multiple
     *                                     sizes (e.g. 96x96 + 192x192 + SVG) is recommended for best rendering
     *                                     across OS UI contexts (jump-lists, lock-screen menus, launcher tiles).
     * @param  int  $order
     *                      Internal sort key used to deterministically order shortcuts in the
     *                      generated manifest. Lower values appear first. NOT serialized into
     *                      the manifest itself — the W3C spec does not define an ordering field;
     *                      browsers render shortcuts in array order.
     *
     * @see https://www.w3.org/TR/manifest-app-info/#shortcutitem-and-its-members
     */
    public function __construct(
        public string $name,
        public string $url,
        public array $icons = [],
        public int $order = 100,
    ) {}

    /**
     * Serializes this shortcut to the W3C ShortcutItem JSON structure.
     *
     * The `icons` key is omitted entirely when no icons are defined — browsers
     * interpret this as "use the application icon as fallback for this shortcut".
     *
     * @return array{name: string, url: string, icons?: list<array<string, string>>}
     */
    public function toManifestArray(): array
    {
        $entry = [
            'name' => $this->name,
            'url' => $this->url,
        ];

        if ($this->icons !== []) {
            $entry['icons'] = array_map(
                fn (ShortcutIcon $icon) => $icon->toArray(),
                $this->icons,
            );
        }

        return $entry;
    }
}
