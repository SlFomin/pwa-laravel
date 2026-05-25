<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Tests\Fixtures;

use SlFomin\PwaLaravel\Attributes\PwaIconSet;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

/**
 * Controller with two PwaIconSet attributes sharing the same name — triggers
 * AmbiguousIconSetException when the registry loads this class.
 */
#[PwaIconSet(name: 'auth', icons: [new ShortcutIcon('/icons/a.png', '96x96')])]
#[PwaIconSet(name: 'auth', icons: [new ShortcutIcon('/icons/b.png', '192x192')])]
class AmbiguousIconSetController
{
    public function someMethod(): void {}
}
