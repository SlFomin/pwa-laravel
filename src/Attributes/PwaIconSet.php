<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Attributes;

use SlFomin\PwaLaravel\Core\Shortcuts\IconSetRegistry;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

/**
 * Declares a named icon set scoped to the controller class.
 *
 * Icon sets enable reuse of icon definitions across multiple {@see PwaShortcut}
 * declarations. Sets declared at the class level are visible to all shortcuts
 * defined on methods of the same class. For application-wide sharing, declare
 * sets in `config('pwa.icon_sets')` instead.
 *
 * Resolution order when a shortcut references `iconSet: 'foo'`:
 *
 * 1. Class-local `PwaIconSet` attributes on the declaring controller.
 * 2. Global config `pwa.icon_sets.foo`.
 *
 * If the same name exists in both scopes, the class-local definition wins.
 * If neither scope defines the name, `IconSetNotFoundException` is thrown.
 *
 * @see PwaShortcut
 * @see IconSetRegistry
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final readonly class PwaIconSet
{
    /**
     * @param  string  $name
     *                        Identifier of this set. Must match the value used in
     *                        `PwaShortcut::$iconSet` to bind a shortcut to this set.
     *                        Case-sensitive. Recommended: lowercase, dash-separated.
     * @param  list<ShortcutIcon>  $icons
     *                                     Icon variants in this set. Same structure as `PwaShortcut::$icons`.
     */
    public function __construct(
        public string $name,
        public array $icons,
    ) {}
}
