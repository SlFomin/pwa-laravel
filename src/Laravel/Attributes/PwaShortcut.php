<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Laravel\Attributes;

use SlFomin\PwaLaravel\Core\Exceptions\InvalidShortcutDefinitionException;
use SlFomin\PwaLaravel\Core\Shortcuts\Shortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

/**
 * Declares a PWA shortcut on a controller method.
 *
 * The shortcut's URL is auto-derived from the route URI mapped to the method.
 * This attribute is REPEATABLE: a single method may declare multiple shortcuts
 * (useful when one action handles multiple semantic entry points).
 *
 * ## Icon specification (mutually exclusive)
 *
 * Exactly one of the following icon parameters may be used per shortcut:
 *
 * 1. `icon: string` — shorthand for a single icon by URL.
 *                     `sizes` and `type` parameters apply ONLY to this form.
 *                     Auto-probe attempts to determine missing metadata.
 *
 * 2. `icon: ShortcutIcon` — single icon with full control (sizes, type, purpose).
 *
 * 3. `icons: array<ShortcutIcon>` — multiple icon variants for different
 *                                    sizes/formats/purposes.
 *
 * If none are provided, the shortcut has no own icon; browsers fall back to
 * the main app icon.
 *
 * @see https://www.w3.org/TR/manifest-app-info/#shortcuts-member
 * @see Shortcut
 * @see ShortcutIcon
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final readonly class PwaShortcut
{
    /**
     * @param  string  $name
     *                        Human-readable shortcut label. Required by W3C spec.
     * @param  string|ShortcutIcon|null  $icon
     *                                          Single-icon shorthand. Accepts:
     *                                          - null         — no own icon (fallback to app icon).
     *                                          - string       — URL of the icon; sizes/type may be auto-probed.
     *                                          - ShortcutIcon — fully specified single icon.
     *                                          Mutually exclusive with `$icons`.
     * @param  string|null  $sizes
     *                              Size hint, applied ONLY when `$icon` is a string.
     *                              Overrides the auto-probe result. Format: "192x192" or "any".
     *                              Ignored otherwise.
     * @param  string|null  $type
     *                             MIME type hint, applied ONLY when `$icon` is a string. See `$sizes`.
     * @param  list<ShortcutIcon>|null  $icons
     *                                          Explicit array of icon variants. Use when the shortcut needs multiple
     *                                          sizes/formats/purposes. Mutually exclusive with `$icon`.
     * @param  int  $order
     *                      Sort key for ordering shortcuts in the manifest. Lower values appear
     *                      first. Defaults to 100 to leave room for higher-priority entries.
     *
     * @throws InvalidShortcutDefinitionException
     *                                            When mutually-exclusive icon parameters are combined, or when
     *                                            `$sizes`/`$type` are used with a non-string `$icon`.
     */
    public function __construct(
        public string $name,
        public string|ShortcutIcon|null $icon = null,
        public ?string $sizes = null,
        public ?string $type = null,
        public ?array $icons = null,
        public int $order = 100,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $specifiedSources = (int) ($this->icon !== null)
            + (int) ($this->icons !== null);

        if ($specifiedSources > 1) {
            throw new InvalidShortcutDefinitionException(
                "PwaShortcut '{$this->name}': use only one of `icon`, `icons`."
            );
        }

        if (($this->sizes !== null || $this->type !== null) && ! is_string($this->icon)) {
            throw new InvalidShortcutDefinitionException(
                "PwaShortcut '{$this->name}': `sizes`/`type` parameters are only valid "
                .'when `icon` is a string URL. For ShortcutIcon or icons[], specify '
                .'these properties inside the ShortcutIcon constructor.'
            );
        }
    }
}
