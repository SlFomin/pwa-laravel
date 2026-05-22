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
use SlFomin\PwaLaravel\Laravel\Attributes\PwaShortcut;
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
| `icon` | `string\|ShortcutIcon\|null` | Single-icon shorthand. Mutually exclusive with `icons`. |
| `sizes` | `string\|null` | Size hint — only valid when `icon` is a string URL (e.g. `"192x192"`). |
| `type` | `string\|null` | MIME type hint — only valid when `icon` is a string URL. |
| `icons` | `ShortcutIcon[]\|null` | Explicit icon variants. Mutually exclusive with `icon`. |
| `order` | `int` | Sort key for manifest order. Lower = first. Default: `100`. |

The attribute is **repeatable**: a single method may carry multiple `#[PwaShortcut]` instances.

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
| **Core** | `SlFomin\PwaLaravel\Core\Shortcuts` | Framework-agnostic DTOs (`Shortcut`, `ShortcutIcon`, `ShortcutCollection`), interfaces (`ShortcutDiscoverer`, `IconResolver`, `IconMetadataProbe`), and default implementations |
| **Laravel** | `SlFomin\PwaLaravel\Laravel\Shortcuts` | Route scanning (`RouteAttributeDiscoverer`), caching (`CachedDiscoverer`), Artisan commands, PHP attributes |

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