<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Core\Shortcuts;

use SlFomin\PwaLaravel\Core\Exceptions\InvalidShortcutDefinitionException;

/**
 * Resolves the final list of icons for a shortcut from the user's declaration.
 *
 * Encapsulates the logic of: shorthand expansion (string → ShortcutIcon),
 * explicit-list passthrough, icon set lookup (v1.3+), and auto-probe of
 * missing sizes/type metadata.
 *
 * Implementations may consult external sources: filesystem (auto-probe),
 * config files, named icon set registries.
 */
interface IconResolver
{
    /**
     * @param  IconResolutionRequest  $request
     *                                          User-declared icon spec from the attribute.
     * @return list<ShortcutIcon>
     *                            Final resolved icons. Empty list is valid (no icons).
     *
     * @throws InvalidShortcutDefinitionException
     */
    public function resolve(IconResolutionRequest $request): array;
}
