<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Tests\Fixtures;

use SlFomin\PwaLaravel\Attributes\PwaShortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

/**
 * Dummy controller used by RouteAttributeDiscoverer tests.
 * Must NOT extend any Laravel base class — it is never actually dispatched.
 */
class ShortcutTestController
{
    #[PwaShortcut(name: 'Login', icon: '/icons/login.png', order: 10)]
    public function showLogin(): void {}

    #[PwaShortcut(name: 'Register', order: 20)]
    #[PwaShortcut(name: 'Sign Up', order: 5)]
    public function showRegister(): void {}

    #[PwaShortcut(
        name: 'Dashboard',
        icons: [
            new ShortcutIcon('/icons/dash-96.png', '96x96', 'image/png'),
            new ShortcutIcon('/icons/dash-192.png', '192x192', 'image/png'),
        ],
        order: 30,
    )]
    public function dashboard(): void {}

    public function noShortcut(): void {}
}
