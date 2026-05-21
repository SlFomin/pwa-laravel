# JS Companion Package

`@slfomin/pwa-laravel` is the JavaScript companion shipped alongside the PHP package. It provides:

- `laravelPwa()` — a Vite plugin wrapping `vite-plugin-pwa` with Laravel-specific defaults
- `setupPwa()` / `registerSW()` — programmatic service worker registration
- `usePwa()` composables for Vue 3, React 19, and Svelte 5

---

## Installation options

### Option A — via npm (recommended for most projects)

```bash
ddev npm install -D vite-plugin-pwa @slfomin/pwa-laravel
```

Then import normally:

```js
import { laravelPwa } from '@slfomin/pwa-laravel';
import { usePwa } from '@slfomin/pwa-laravel/vue';   // or /react or /svelte
```

### Option B — from the composer vendor directory (no separate npm install)

Pre-built `dist/` files are committed to the repository and shipped inside the composer package.
After `ddev composer install`, they are available at `vendor/slfomin/pwa-laravel/dist/`.

```js
// vite.config.js
import { laravelPwa } from '../../vendor/slfomin/pwa-laravel/dist/index.js';
```

```js
// In your app JS (optional framework composables)
import { usePwa } from '../../vendor/slfomin/pwa-laravel/dist/vue.js';
import { usePwa } from '../../vendor/slfomin/pwa-laravel/dist/react.js';
import { usePwa } from '../../vendor/slfomin/pwa-laravel/dist/svelte.js';
```

> **Note:** `vite-plugin-pwa` is still required as a peer dependency even when using Option B —
> install it with `ddev npm install -D vite-plugin-pwa`.

**Using a Vite alias (cleaner vendor path):**

```js
// vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// Read the pre-built plugin from the vendor directory
const { laravelPwa } = await import(
    new URL('../../vendor/slfomin/pwa-laravel/dist/index.js', import.meta.url).href
);

export default defineConfig({
    resolve: {
        alias: {
            '@slfomin/pwa-laravel': '/vendor/slfomin/pwa-laravel/dist/index.js',
            '@slfomin/pwa-laravel/vue': '/vendor/slfomin/pwa-laravel/dist/vue.js',
        },
    },
    plugins: [
        laravel({ input: ['resources/css/app.css', 'resources/js/app.js'], refresh: true }),
        laravelPwa({ strategies: 'generateSW', manifest: false }),
    ],
});
```

With aliases, your app code can keep `import { usePwa } from '@slfomin/pwa-laravel/vue'` and Vite
resolves it to the vendor path automatically.

---

## What's in dist/

| File | Contents |
|---|---|
| `dist/index.js` | `laravelPwa()` Vite plugin + `setupPwa()` |
| `dist/vue.js` | `usePwa()` composable for Vue 3 / Inertia v3 |
| `dist/react.js` | `usePwa()` hook for React 19 / Inertia v3 |
| `dist/svelte.js` | `usePwa()` rune helper for Svelte 5 / Inertia v3 |
| `dist/*.d.ts` | TypeScript declarations |
| `dist/*.js.map` | Source maps |

All files are ESM-only. No CommonJS output.

---

## `laravelPwa()` options

```ts
laravelPwa({
    // --- Laravel-specific ---
    inertia?: boolean;             // true → navigateFallback:'/', API routes excluded
    excludeFromSW?: (string | RegExp)[];  // extra URL patterns to deny
    navigateFallback?: string | null;     // override navigate fallback URL

    // --- All vite-plugin-pwa VitePWAOptions are passed through ---
    strategies?: 'generateSW' | 'injectManifest';
    manifest?: false;     // always false — manifest is served by Laravel
    workbox?: WorkboxOptions;
    // ...
})
```

### Laravel defaults applied automatically

| Option | Default | Why |
|---|---|---|
| `registerType` | `'autoUpdate'` | Blade directive handles registration |
| `injectRegister` | `null` | `@pwaRegisterSW` renders the inline script |
| `workbox.cleanupOutdatedCaches` | `true` | Prevents stale cache buildup |
| `workbox.clientsClaim` | `true` | New SW takes over immediately |
| `workbox.skipWaiting` | `false` | Combined with `@pwaRegisterSW`'s `SKIP_WAITING` message |
| `devOptions.enabled` | `false` | Never register SW during development |

---

## Rebuilding dist/

The `dist/` files are pre-built and committed to the repository. You only need to rebuild when
modifying the TypeScript source:

```bash
ddev npm run build     # one-off build
ddev npm run dev       # watch mode
```

The `prepare` lifecycle script runs automatically on `npm install`, so installing from a git URL
always produces a fresh build.
