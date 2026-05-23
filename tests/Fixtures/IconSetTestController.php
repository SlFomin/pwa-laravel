<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Tests\Fixtures;

use SlFomin\PwaLaravel\Attributes\PwaIconSet;
use SlFomin\PwaLaravel\Attributes\PwaShortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

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
