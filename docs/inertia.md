# Inertia.js v3 Integration

The package has first-class support for [Inertia.js v3](https://inertiajs.com/). All Inertia code
is isolated in `src/Inertia/` and is only activated when `inertiajs/inertia-laravel ^3.0` is
detected at runtime.

---

## Requirements

```bash
ddev composer require inertiajs/inertia-laravel:^3.0
```

The package auto-detects Inertia and enables the adapter when `pwa.inertia.auto_detect` is `true`
(the default).

---

## Vite config

```js
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import inertia from '@inertiajs/vite';
import { laravelPwa } from '@slfomin/pwa-laravel';

export default defineConfig({
    plugins: [
        laravel({ input: ['resources/css/app.css', 'resources/js/app.js'], refresh: true }),
        inertia(),
        laravelPwa({
            inertia: true,          // navigateFallback: '/', API routes excluded
            strategies: 'generateSW',
            manifest: false,        // manifest is served by Laravel
            devOptions: { enabled: false }, // must be off while inertia() is active
        }),
    ],
});
```

`inertia: true` in the plugin options does two things:

1. Sets `workbox.navigateFallback` to `'/'` so the SW returns the app shell for all navigation requests.
2. Adds default deny-list entries so `/api/*`, `/sanctum/*`, `/livewire/*`, etc. are never handled by the SW navigation fallback.

---

## Shared props

`InertiaAdapter` calls `Inertia::share('pwa', fn () => [...])` on every request. Every page
component receives:

```ts
{
    pwa: {
        manifest_url: string,       // e.g. '/manifest.webmanifest'
        sw: {
            url: string,            // '/sw.js'
            scope: string,          // '/'
            register_type: 'autoUpdate' | 'prompt',
            auto_register: boolean,
            available: boolean,     // false before first Vite build
        },
        navigate_fallback: string | null,
        is_ssr: boolean,
    }
}
```

Disable sharing:

```php
// config/pwa.php
'inertia' => [
    'share_props' => false,
],
```

Change the prop key (default `pwa`):

```php
'inertia' => [
    'shared_prop_key' => 'app_pwa',
],
```

---

## `usePwa()` — Vue 3

```ts
import { usePwa } from '@slfomin/pwa-laravel/vue';

const { manifestUrl, swInfo, navigateFallback, isOffline } = usePwa();
```

| Return value | Type | Description |
|---|---|---|
| `manifestUrl` | `ComputedRef<string \| undefined>` | Manifest URL from shared props |
| `swInfo` | `ComputedRef<...>` | SW config object |
| `navigateFallback` | `ComputedRef<string \| null>` | Navigate fallback URL |
| `isOffline` | `ComputedRef<boolean>` | `!navigator.onLine` |

---

## `usePwa()` — React 19

```ts
import { usePwa } from '@slfomin/pwa-laravel/react';

const { manifestUrl, swInfo, navigateFallback, isOffline } = usePwa();
```

Same return shape as the Vue composable; uses `usePage()` from `@inertiajs/react`.

---

## `usePwa()` — Svelte 5

```ts
import { usePwa } from '@slfomin/pwa-laravel/svelte';

const { manifestUrl, swInfo, navigateFallback, isOffline } = usePwa();
```

Returns reactive `$derived` runes backed by `usePage()` from `@inertiajs/svelte`.

---

## InertiaPwaMiddleware

Apply `pwa.inertia` to your Inertia routes to set correct caching headers. Without it the service
worker may cache Inertia partial-reload responses as if they were full HTML pages.

```php
// routes/web.php
Route::middleware(['web', 'pwa.inertia'])->group(function () {
    Route::inertia('/', 'Home');
    Route::inertia('/dashboard', 'Dashboard');
});
```

What the middleware does on every Inertia XHR request:

- Adds `Vary: X-Inertia, Accept` so caches treat XHR and full-page requests as separate responses.
- Adds `X-PWA-Inertia: 1` header for debugging.
- Appends `no-store` to `Cache-Control` so the SW never puts partial responses in its cache.

---

## SSR

SSR support is enabled via:

```env
PWA_INERTIA_SSR=true
```

`InertiaDetector::isSsr()` checks for the `X-Inertia-SSR` request header used by Inertia v3's SSR
runtime. The `is_ssr` flag in shared props reflects this.

---

## Excluding additional routes from the SW

Add patterns to `pwa.inertia.exclude_from_sw` or pass `excludeFromSW` to the Vite plugin:

```js
laravelPwa({
    inertia: true,
    excludeFromSW: ['/admin/*', /^\/legacy\//],
})
```
