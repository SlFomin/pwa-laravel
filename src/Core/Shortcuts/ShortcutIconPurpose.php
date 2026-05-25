<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Core\Shortcuts;

/**
 * Enumerates valid `purpose` tokens for {@see ShortcutIcon}.
 *
 * Spec defines these as space-separated tokens; this enum represents one token.
 * For multi-purpose icons use the string form: `purpose: 'any maskable'`.
 *
 * @see https://www.w3.org/TR/manifest/#purpose-member
 */
enum ShortcutIconPurpose: string
{
    case Any = 'any';
    case Maskable = 'maskable';
    case Monochrome = 'monochrome';
}
