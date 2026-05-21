# pwa-laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/slfomin/pwa-laravel.svg?style=flat-square)](https://packagist.org/packages/slfomin/pwa-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/slfomin/pwa-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/slfomin/pwa-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/slfomin/pwa-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/slfomin/pwa-laravel/actions?query=workflow%3A%22Fix+PHP+code+style+issues%22+branch%3Amain)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat-square)](https://phpstan.org)
[![Total Downloads](https://img.shields.io/packagist/dt/slfomin/pwa-laravel.svg?style=flat-square)](https://packagist.org/packages/slfomin/pwa-laravel)

Full PWA integration for **Laravel 13 + Vite 7** with optional **Inertia.js v3** support.

- Static or dynamic Web App Manifest (per-locale, per-tenant via resolvers)
- Service Worker registration via Blade directives
- Automatic icon generation (standard, maskable, apple-touch, favicon) via `intervention/image`
- Inertia v3 adapter: shared PWA props, middleware for correct SW/Inertia caching
- JS companion package `@slfomin/pwa-laravel` wrapping `vite-plugin-pwa`
- PHPStan level 8, PHP 8.4+

## Requirements

| Dependency | Version |
|---|---|
| PHP | ^8.4 |
| Laravel | ^13.0 |
| Vite | ^6.0 \|\| ^7.0 |
| vite-plugin-pwa | ^1.0 |
| Inertia.js (optional) | ^3.0 |

## Installation

```bash
ddev composer require slfomin/pwa-laravel
```

Run the interactive installer:

```bash
ddev artisan pwa:install
```

This publishes `config/pwa.php` and prints the next steps.

## Quick Start

### 1. Place your source icon

Put a square PNG (512×512 or larger) at `resources/images/pwa-icon.png`, then generate all sizes:

```bash
ddev artisan pwa:generate-icons
```

Icons are written to `public/icons/` and cover standard, maskable, Apple Touch, and favicon sizes.

### 2. Add Blade directives to your layout

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @pwaMeta
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    {{ $slot }}
    @pwaRegisterSW
</body>
</html>
```

`@pwaMeta` renders the `<link rel="manifest">`, theme-color, apple-touch-icon, favicons, and other mobile meta tags.

`@pwaRegisterSW` injects a small inline script that registers the service worker on page load.

### 3. Install the JS companion

**Via npm (recommended):**

```bash
ddev npm install -D vite-plugin-pwa @slfomin/pwa-laravel
```

**From the composer package (no npm required):**

Pre-built files are shipped inside the composer package at `vendor/slfomin/pwa-laravel/dist/`.
Import directly in `vite.config.js`:

```js
import { laravelPwa } from '../../vendor/slfomin/pwa-laravel/dist/index.js';
// framework composables (optional):
// import { usePwa } from '../../vendor/slfomin/pwa-laravel/dist/vue.js';
```

> `vite-plugin-pwa` is still a required peer dependency — install it with
> `ddev npm install -D vite-plugin-pwa`.

### 4. Configure Vite

```js
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { laravelPwa } from '@slfomin/pwa-laravel';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        laravelPwa({
            strategies: 'generateSW',
            manifest: false, // manifest is served by Laravel, not Vite
            workbox: {
                globPatterns: ['**/*.{js,css,html,ico,png,svg,webp,woff,woff2}'],
            },
        }),
    ],
});
```

### 5. Build

```bash
ddev npm run build
```

That's it — your Laravel app is now a PWA.

---

## Inertia.js v3 Integration

### Vite config

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import inertia from '@inertiajs/vite';
import { laravelPwa } from '@slfomin/pwa-laravel';

export default defineConfig({
    plugins: [
        laravel({ input: ['resources/css/app.css', 'resources/js/app.js'], refresh: true }),
        inertia(),
        laravelPwa({
            inertia: true,          // sets navigateFallback: '/' and excludes API routes
            strategies: 'generateSW',
            manifest: false,
            devOptions: { enabled: false }, // must be off when inertia() plugin is active
        }),
    ],
});
```

### Shared props

The package automatically calls `Inertia::share('pwa', ...)` if Inertia is installed and
`pwa.inertia.auto_detect` is `true` (default). Every page receives:

```ts
{
    pwa: {
        manifest_url: string,
        sw: { url, scope, register_type, auto_register, available },
        navigate_fallback: string | null,
        is_ssr: boolean,
    }
}
```

### `usePwa()` — Vue 3

```ts
import { usePwa } from '@slfomin/pwa-laravel/vue';

const { manifestUrl, swInfo, navigateFallback, isOffline } = usePwa();
```

### `usePwa()` — React 19

```ts
import { usePwa } from '@slfomin/pwa-laravel/react';

const { manifestUrl, swInfo, navigateFallback, isOffline } = usePwa();
```

### Middleware

Add `pwa.inertia` to your routes to set correct `Vary` and `Cache-Control` headers,
preventing the service worker from caching Inertia XHR responses:

```php
// routes/web.php
Route::middleware(['web', 'pwa.inertia'])->group(function () {
    // your Inertia routes
});
```

---

## Dynamic Manifest

Switch the driver to `dynamic` to serve a manifest generated by Laravel on each request:

```php
// config/pwa.php
'manifest' => [
    'driver' => 'dynamic',
    'dynamic' => [
        'resolver' => \App\Pwa\TenantManifestResolver::class,
        'cache' => true,
        'cache_ttl' => 3600,
    ],
],
```

Implement your resolver:

```php
namespace App\Pwa;

use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Contracts\ManifestResolver;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

final class TenantManifestResolver implements ManifestResolver
{
    public function resolve(Request $request, ManifestBuilder $default): ManifestBuilder
    {
        $tenant = $request->user()?->tenant;

        return $tenant
            ? $default->name($tenant->name)->themeColor($tenant->brand_color)
            : $default;
    }

    public function cacheKey(Request $request): ?string
    {
        return 'tenant.'.($request->user()?->tenant_id ?? 'guest');
    }
}
```

Bind it in `AppServiceProvider`:

```php
$this->app->bind(
    \SlFomin\PwaLaravel\Contracts\ManifestResolver::class,
    \App\Pwa\TenantManifestResolver::class,
);
```

---

## Blade Directives

| Directive | Output |
|---|---|
| `@pwaMeta` | `<link rel="manifest">`, theme-color, apple-touch-icon, favicons, mobile meta |
| `@pwaRegisterSW` | Inline `<script>` registering the service worker |
| `@pwaInstallButton('Install')` | A hidden `<button>` that appears when the browser's install prompt fires |

---

## Artisan Commands

| Command | Description |
|---|---|
| `pwa:install` | Interactive installer — publishes config and prints next steps |
| `pwa:generate-icons` | Generate full icon set from source PNG |
| `pwa:publish-manifest` | Write `manifest.webmanifest` from config (no Vite build required) |

### `pwa:generate-icons`

```
pwa:generate-icons [source] [--output=] [--dry-run]

  source     Path to source PNG (≥512×512, square). Default: pwa.icons.source from config.
  --output   Output directory. Default: pwa.icons.output_path from config.
  --dry-run  Validate source without writing any files.
```

### `pwa:publish-manifest`

```
pwa:publish-manifest [--path=] [--pretty]

  --path    Output file path. Default: pwa.manifest.static_path from config.
  --pretty  Pretty-print the JSON output.
```

---

## Configuration Reference

All options live in `config/pwa.php`. Key `.env` variables:

| Variable | Default | Description |
|---|---|---|
| `PWA_MANIFEST_DRIVER` | `static` | `static` (Vite file) or `dynamic` (Laravel controller) |
| `PWA_MANIFEST_ROUTE` | `/manifest.webmanifest` | URL the browser fetches |
| `APP_NAME` | `Laravel` | PWA full name |
| `PWA_SHORT_NAME` | `APP_NAME` | Short name for the home screen icon |
| `PWA_DESCRIPTION` | `` | PWA description |
| `PWA_DISPLAY` | `standalone` | Display mode (`standalone`, `fullscreen`, `minimal-ui`, `browser`) |
| `PWA_THEME_COLOR` | `#000000` | Theme / status-bar color |
| `PWA_BG_COLOR` | `#ffffff` | Splash screen background color |
| `PWA_SW_STRATEGY` | `generateSW` | `generateSW` or `injectManifest` |
| `PWA_SW_URL` | `/sw.js` | Service worker registration URL |
| `PWA_SW_DEV` | `false` | Enable service worker in local dev |
| `PWA_MANIFEST_CACHE` | `true` | Cache dynamic manifest responses |
| `PWA_MANIFEST_CACHE_TTL` | `3600` | Cache TTL in seconds |

---

## Facade & Contracts

```php
use SlFomin\PwaLaravel\Facades\Pwa;

Pwa::manifest();         // ManifestBuilder for the current request
Pwa::manifestUrl();      // string — URL of the manifest
Pwa::serviceWorkerUrl(); // string — URL of the service worker
Pwa::worker();           // WorkerManager instance
Pwa::driver();           // ManifestDriver instance
```

Extension points in `SlFomin\PwaLaravel\Contracts`:

| Contract | Purpose |
|---|---|
| `ManifestDriver` | Custom manifest delivery strategy |
| `ManifestResolver` | Context-aware manifests (tenant, locale, user role) |
| `IconGenerator` | Replace `intervention/image` with another library |
| `ServiceWorkerStrategy` | Extend Vite plugin options |

---

## Testing

```bash
ddev composer test      # Pest (119 tests)
ddev composer analyse   # PHPStan level 8
ddev composer format    # Laravel Pint
ddev composer ci        # all three in sequence
```

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

Please review [our security policy](../../security/policy) to report vulnerabilities.

## Credits

- [Vyacheslav Brynzevich](https://github.com/SlFomin)
- [All Contributors](../../contributors)

## License

MIT. Please see [License File](LICENSE.md) for more information.
