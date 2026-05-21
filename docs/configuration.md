# Configuration Reference

All options are published to `config/pwa.php` after running `ddev artisan pwa:install`.

---

## Manifest

```php
'manifest' => [
    'driver'      => env('PWA_MANIFEST_DRIVER', 'static'),
    'route'       => env('PWA_MANIFEST_ROUTE', '/manifest.webmanifest'),
    'static_path' => public_path('build/manifest.webmanifest'),

    'dynamic' => [
        'resolver'         => \SlFomin\PwaLaravel\Manifest\Resolvers\DefaultManifestResolver::class,
        'cache'            => env('PWA_MANIFEST_CACHE', true),
        'cache_ttl'        => env('PWA_MANIFEST_CACHE_TTL', 3600),
        'cache_key_prefix' => 'pwa.manifest.',
        'cache_store'      => env('PWA_MANIFEST_CACHE_STORE'), // null = default store
    ],

    'data' => [
        'name'            => env('APP_NAME', 'Laravel'),
        'short_name'      => env('PWA_SHORT_NAME', env('APP_NAME', 'Laravel')),
        'description'     => env('PWA_DESCRIPTION', ''),
        'start_url'       => '/',
        'scope'           => '/',
        'display'         => env('PWA_DISPLAY', 'standalone'),
        'orientation'     => 'any',
        'background_color'=> env('PWA_BG_COLOR', '#ffffff'),
        'theme_color'     => env('PWA_THEME_COLOR', '#000000'),
        'lang'            => env('APP_LOCALE', 'en'),
        'dir'             => 'ltr',
        'categories'      => [],
        'icons'           => [],
        'shortcuts'       => [],
        'screenshots'     => [],
    ],
],
```

### Key `.env` variables

| Variable | Default | Description |
|---|---|---|
| `PWA_MANIFEST_DRIVER` | `static` | `static` — file from `public/build/`; `dynamic` — Laravel controller |
| `PWA_MANIFEST_ROUTE` | `/manifest.webmanifest` | URL the browser fetches |
| `APP_NAME` | `Laravel` | PWA full name |
| `PWA_SHORT_NAME` | `APP_NAME` | Short name for the home screen label |
| `PWA_DESCRIPTION` | `` | Short description shown in app stores |
| `PWA_DISPLAY` | `standalone` | `standalone`, `fullscreen`, `minimal-ui`, or `browser` |
| `PWA_THEME_COLOR` | `#000000` | Status-bar / title-bar color |
| `PWA_BG_COLOR` | `#ffffff` | Splash-screen background |
| `PWA_MANIFEST_CACHE` | `true` | Cache dynamic manifests |
| `PWA_MANIFEST_CACHE_TTL` | `3600` | Cache TTL in seconds |

---

## Icons

```php
'icons' => [
    'source'              => resource_path('images/pwa-icon.png'),
    'output_path'         => public_path('icons'),
    'output_url_prefix'   => '/icons',
    'sizes'               => [72, 96, 128, 144, 152, 192, 384, 512],
    'generate_maskable'   => true,
    'maskable_sizes'      => [192, 512],
    'maskable_padding'    => 0.1,   // 10% safe-zone on each side
    'maskable_background' => null,  // null → uses manifest.background_color
    'generate_apple_touch'=> true,
    'apple_touch_size'    => 180,
    'generate_favicon'    => true,
    'favicon_sizes'       => [16, 32],
    'format'              => 'png',
    'quality'             => 90,
],
```

---

## Service Worker

```php
'service_worker' => [
    'strategy'      => env('PWA_SW_STRATEGY', 'generateSW'),
    'url'           => env('PWA_SW_URL', '/sw.js'),
    'scope'         => '/',
    'auto_register' => true,
    'register_type' => 'autoUpdate',  // 'autoUpdate' | 'prompt'
    'dev_enabled'   => env('PWA_SW_DEV', false),
],
```

| Variable | Default | Description |
|---|---|---|
| `PWA_SW_STRATEGY` | `generateSW` | `generateSW` (Workbox) or `injectManifest` (custom SW) |
| `PWA_SW_URL` | `/sw.js` | Service worker URL |
| `PWA_SW_DEV` | `false` | Register SW in `local` environment |

---

## Inertia

```php
'inertia' => [
    'auto_detect'       => true,
    'share_props'       => true,
    'shared_prop_key'   => 'pwa',
    'navigate_fallback' => '/',
    'exclude_from_sw'   => [
        '/api/*', '/sanctum/*', '/broadcasting/*',
        '/livewire/*', '/horizon/*', '/telescope/*', '/pulse/*',
    ],
    'ssr_enabled' => env('PWA_INERTIA_SSR', false),
],
```

---

## HTTP Headers

```php
'headers' => [
    'manifest' => [
        'Content-Type'  => 'application/manifest+json',
        'Cache-Control' => 'public, max-age=3600',
    ],
    'service_worker' => [
        'Content-Type'          => 'application/javascript; charset=utf-8',
        'Cache-Control'         => 'no-cache, no-store, must-revalidate',
        'Service-Worker-Allowed'=> '/',
    ],
],
```

---

## Vite Integration

```php
'vite' => [
    'manifest_path' => public_path('build/manifest.json'),
    'build_path'    => public_path('build'),
    'base_url'      => '/build/',
],
```

These paths are used by `ViteManifestBridge` to resolve hashed asset filenames. Override them if your Vite build output lives outside `public/build/`.

---

## config:cache and filesystem paths

Several config values call `public_path()` or `resource_path()` at load time. When you run
`php artisan config:cache`, those paths are **baked in** for the machine that ran the command.

If your build pipeline and runtime server are different machines (common in Docker or CI/CD
deploy workflows), the cached paths will point to the build machine's filesystem and fail at
runtime.

**Affected keys and their override env variables:**

| Config key | Env variable |
|---|---|
| `manifest.static_path` | `PWA_STATIC_MANIFEST_PATH` |
| `icons.source` | `PWA_ICON_SOURCE` |
| `icons.output_path` | `PWA_ICON_OUTPUT_PATH` |
| `vite.manifest_path` | `PWA_VITE_MANIFEST_PATH` |
| `vite.build_path` | `PWA_VITE_BUILD_PATH` |

Set these env variables to absolute paths in your production `.env` file when using
`config:cache` across machines.
