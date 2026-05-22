<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Laravel\Attributes;

use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final readonly class PwaIconSet
{
    /** @param list<ShortcutIcon> $icons */
    public function __construct(
        public string $name,
        public array $icons,
    ) {}
}
