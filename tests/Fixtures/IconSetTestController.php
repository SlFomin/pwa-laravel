<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Tests\Fixtures;

use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;
use SlFomin\PwaLaravel\Laravel\Attributes\PwaIconSet;
use SlFomin\PwaLaravel\Laravel\Attributes\PwaShortcut;

/**
 * Controller with class-scoped PwaIconSet attributes, used by icon-set tests.
 */
#[PwaIconSet(name: 'auth', icons: [
    new ShortcutIcon('/icons/auth-96.png', '96x96', 'image/png'),
    new ShortcutIcon('/icons/auth-192.png', '192x192', 'image/png'),
])]
#[PwaIconSet(name: 'admin', icons: [
    new ShortcutIcon('/icons/admin-96.png', '96x96', 'image/png'),
])]
class IconSetTestController
{
    #[PwaShortcut(name: 'Login', iconSet: 'auth', order: 10)]
    public function showLogin(): void {}

    #[PwaShortcut(name: 'Admin', iconSet: 'admin', order: 20)]
    public function adminDashboard(): void {}
}

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
