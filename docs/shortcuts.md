# PWA Shortcuts

PWA shortcuts are quick-action entries displayed in the OS context menu
(long-press on the app icon on mobile, right-click on desktop).
They are defined in the `shortcuts` field of the Web App Manifest.

> **Spec**: [W3C Web App Manifest — shortcuts member](https://www.w3.org/TR/manifest-app-info/#shortcuts-member)

---

## Declaring shortcuts via PHP attributes

Place `#[PwaShortcut]` on any controller method that is mapped to a route.
The shortcut URL is auto-derived from the route URI; the icon is optional.

```php
use SlFomin\PwaLaravel\Attributes\PwaShortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

class AuthController
{
    // String shorthand — sizes/type are auto-probed from public/
    #[PwaShortcut(name: 'Login', icon: '/icons/login.png')]
    public function showLogin() {}

    // ShortcutIcon object — full control over sizes, type, purpose
    #[PwaShortcut(
        name: 'Recover Password',
        icon: new ShortcutIcon('/icons/recover.svg', 'any', 'image/svg+xml'),
    )]
    public function recover() {}

    // Multiple icon variants for different sizes/formats
    #[PwaShortcut(
        name: 'Register',
        icons: [
            new ShortcutIcon('/icons/register-96.png', '96x96'),
            new ShortcutIcon('/icons/register-192.png', '192x192'),
        ],
    )]
    public function showRegister() {}
}
```

### `#[PwaShortcut]` parameters

| Parameter | Type | Description |
|---|---|---|
| `name` | `string` | Required. Label shown in the OS menu. |
| `icon` | `string\|ShortcutIcon\|null` | Single-icon shorthand. Mutually exclusive with `icons` and `iconSet`. |
| `sizes` | `string\|null` | Size hint — only valid when `icon` is a string URL (e.g. `"192x192"`). |
| `type` | `string\|null` | MIME type hint — only valid when `icon` is a string URL. |
| `icons` | `ShortcutIcon[]\|null` | Explicit icon variants. Mutually exclusive with `icon` and `iconSet`. |
| `iconSet` | `string\|null` | Name of a registered icon set. Mutually exclusive with `icon` and `icons`. See [Icon sets](#icon-sets). |
| `order` | `int` | Sort key for manifest order. Lower = first. Default: `100`. |

The attribute is **repeatable**: a single method may carry multiple `#[PwaShortcut]` instances.

---

## Icon sets

Icon sets let you declare a named group of icon variants once and reference it from multiple
`#[PwaShortcut]` attributes instead of repeating the same icon list on every method.
Two scopes are supported: **global** (config) and **class-local** (PHP attribute).

### Global sets via config

Declare sets in `config/pwa.php` under `icon_sets`. Each entry is a list of icon arrays or
`ShortcutIcon` objects:

```php
// config/pwa.php
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

'icon_sets' => [
    'auth' => [
        ['src' => '/icons/auth-96.png', 'sizes' => '96x96', 'type' => 'image/png'],
        new ShortcutIcon('/icons/auth.svg', 'any', 'image/svg+xml'),
    ],
    'admin' => [
        ['src' => '/icons/admin-96.png', 'sizes' => '96x96', 'type' => 'image/png'],
        ['src' => '/icons/admin-192.png', 'sizes' => '192x192', 'type' => 'image/png'],
    ],
],
```

Reference a config set by name from any controller:

```php
class AuthController
{
    #[PwaShortcut(name: 'Login', iconSet: 'auth')]
    public function showLogin() {}

    #[PwaShortcut(name: 'Register', iconSet: 'auth')]
    public function showRegister() {}
}
```

### Class-scoped sets via `#[PwaIconSet]`

For icons used only within one controller, declare sets directly on the class with `#[PwaIconSet]`.
This avoids polluting the global config with single-use definitions:

```php
use SlFomin\PwaLaravel\Attributes\PwaIconSet;
use SlFomin\PwaLaravel\Attributes\PwaShortcut;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

#[PwaIconSet(name: 'auth', icons: [
    new ShortcutIcon('/icons/auth-96.png', '96x96', 'image/png'),
    new ShortcutIcon('/icons/auth-192.png', '192x192', 'image/png'),
])]
class AuthController
{
    #[PwaShortcut(name: 'Login', iconSet: 'auth')]
    public function showLogin() {}

    #[PwaShortcut(name: 'Register', iconSet: 'auth')]
    public function showRegister() {}
}
```

The attribute is **repeatable** — a single class may carry several `#[PwaIconSet]` declarations,
each with a distinct name.

### Resolution order

When a shortcut declares `iconSet: 'foo'`, the package resolves in this order:

1. **Class-local** `#[PwaIconSet]` attributes on the declaring controller.
2. **Global** `config('pwa.icon_sets.foo')`.

Class-local definitions win on name collision. If neither scope defines the name, an
`IconSetNotFoundException` is thrown at discovery time.

### `#[PwaIconSet]` parameters

| Parameter | Type | Description |
|---|---|---|
| `name` | `string` | Required. Identifier for this set. Must match the value used in `PwaShortcut::$iconSet`. Case-sensitive. |
| `icons` | `ShortcutIcon[]` | Required. Icon variants in this set. Same structure as `PwaShortcut::$icons`. |

### Mixing scopes

Nothing prevents a controller from combining class-local and global sets:

```php
#[PwaIconSet(name: 'local-auth', icons: [
    new ShortcutIcon('/icons/auth-96.png', '96x96', 'image/png'),
])]
class AuthController
{
    // uses the class-local set
    #[PwaShortcut(name: 'Login', iconSet: 'local-auth')]
    public function showLogin() {}

    // uses a globally-defined set from config
    #[PwaShortcut(name: 'Admin', iconSet: 'admin')]
    public function adminPanel() {}
}
```

---

## `ShortcutIcon`

```php
new ShortcutIcon(
    src: '/icons/login.png',
    sizes: '192x192',        // optional — auto-probed from public/ if omitted
    type: 'image/png',       // optional — auto-probed from public/ if omitted
    purpose: 'any',          // optional — 'any' | 'maskable' | 'monochrome'
)
```

When `sizes` or `type` are omitted, the package reads the file from `public/`
and fills them automatically (PNG/JPG dimensions via `getimagesize()`, SVG → `"any"`).
Remote URLs (`http://`, `https://`) are never probed.

---

## How shortcuts reach the manifest

### Dynamic driver (recommended)

When `pwa.manifest.driver = dynamic`, `DynamicManifestDriver` injects discovered shortcuts
into the builder automatically **after** the resolver runs, if:

- `pwa.shortcuts.enabled` is `true` (default)
- `manifest.data.shortcuts` in `config/pwa.php` is empty or absent

If `manifest.data.shortcuts` is non-empty, those static shortcuts take priority
and auto-discovery is skipped for that request.

### Static driver

The static driver serves a pre-built `manifest.webmanifest` file produced by Vite.
Shortcuts must be configured inside `vite.config.js` (via `vite-plugin-pwa`'s
`manifest.shortcuts` option) — they are not injected by PHP in this mode.

---

## Artisan commands

```bash
# Inspect discovered shortcuts (current routes scan)
ddev artisan pwa:shortcuts:list

# Inspect without cache
ddev artisan pwa:shortcuts:list --no-cache

# Pre-warm the shortcuts cache (run in deploy pipeline)
ddev artisan pwa:shortcuts:cache

# Flush the shortcuts cache
ddev artisan pwa:shortcuts:clear

# List all globally-registered icon sets (from config)
ddev artisan pwa:icon-sets:list
```

---

## Cache behaviour

By default, caching is **enabled in production** and **disabled in other environments**.
Control it explicitly via config or env:

```php
// config/pwa.php
'shortcuts' => [
    'enabled' => true,
    'cache_enabled' => env('PWA_SHORTCUTS_CACHE', null), // null = auto by APP_ENV
],
```

```dotenv
# .env
PWA_SHORTCUTS_CACHE=true
```

In a deploy pipeline, run `pwa:shortcuts:cache` after `route:cache` to pre-warm
the cache from the freshly cached route list.

---

## Disabling shortcuts

```php
// config/pwa.php
'shortcuts' => [
    'enabled' => false,
],
```

---

## Architecture notes

The shortcuts system is split into two layers:

| Layer | Namespace | Responsibility |
|---|---|---|
| **Core** | `SlFomin\PwaLaravel\Core\Shortcuts` | Framework-agnostic DTOs (`Shortcut`, `ShortcutIcon`, `ShortcutCollection`), interfaces (`ShortcutDiscoverer`, `IconResolver`, `IconMetadataProbe`, `IconSetRegistry`), and default implementations |
| **Laravel** | `SlFomin\PwaLaravel\Laravel\Shortcuts` | Route scanning (`RouteAttributeDiscoverer`), caching (`CachedDiscoverer`), icon-set registries (`ConfigIconSetRegistry`, `AttributeIconSetRegistry`, `CompositeIconSetRegistry`), Artisan commands, PHP attributes |

`ShortcutDiscoverer` is bound in the container — inject it into your own classes
to access the full discovered collection:

```php
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutDiscoverer;

class MyManifestResolver implements ManifestResolver
{
    public function __construct(
        private readonly ShortcutDiscoverer $shortcuts,
    ) {}

    public function resolve(Request $request, ManifestBuilder $default): ManifestBuilder
    {
        // shortcuts are already injected by DynamicManifestDriver,
        // but you can override them here if needed
        return $default;
    }
}
```