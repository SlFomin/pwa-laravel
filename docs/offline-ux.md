# Offline UX

---

## Detecting offline state

Use the `isOffline` value returned by `usePwa()` to show a banner or disable features:

**Vue**
```vue
<script setup>
import { usePwa } from '@slfomin/pwa-laravel/vue';
const { isOffline } = usePwa();
</script>

<template>
    <div v-if="isOffline" class="offline-banner">You are offline.</div>
</template>
```

**React**
```tsx
import { usePwa } from '@slfomin/pwa-laravel/react';

export function OfflineBanner() {
    const { isOffline } = usePwa();
    return isOffline ? <div className="offline-banner">You are offline.</div> : null;
}
```

**Svelte**
```svelte
<script>
import { usePwa } from '@slfomin/pwa-laravel/svelte';
const { isOffline } = usePwa();
</script>

{#if $isOffline}
    <div class="offline-banner">You are offline.</div>
{/if}
```

---

## Offline fallback page with Inertia

Configure Workbox to serve a fallback Inertia page when navigation requests fail offline:

```ts
// vite.config.ts
laravelPwa({
    inertia: true,
    navigateFallback: '/',          // Inertia root — served from SW cache
    workbox: {
        runtimeCaching: [
            {
                urlPattern: ({ request }) => request.mode === 'navigate',
                handler: 'NetworkFirst',
                options: {
                    cacheName: 'pages',
                    networkTimeoutSeconds: 5,
                    expiration: { maxEntries: 50 },
                },
            },
        ],
    },
});
```

The `navigateFallback: '/'` option is set automatically when `inertia: true` is passed.
Your root Inertia page will be cached and served when the network is unavailable.

---

## Caching API responses for offline use

```ts
workbox: {
    runtimeCaching: [
        {
            urlPattern: /^\/api\//,
            handler: 'NetworkFirst',       // try network, fall back to cache
            options: {
                cacheName: 'api-cache',
                networkTimeoutSeconds: 3,
                expiration: {
                    maxEntries: 100,
                    maxAgeSeconds: 60 * 60 * 24,   // 1 day
                },
                cacheableResponse: { statuses: [0, 200] },
            },
        },
    ],
},
```

> **Note:** Avoid caching mutating endpoints (`POST`, `PUT`, `DELETE`).
> Workbox only caches `GET` requests by default.

---

## Inertia shared prop: `isOffline` vs `navigate_fallback`

`usePwa()` exposes two related values:

| Value | Type | Description |
|---|---|---|
| `isOffline` | `boolean` | Current network status (reactive, updates in real time) |
| `navigateFallback` | `string \| null` | URL configured as offline fallback in SW |

Use `isOffline` for UI state. Use `navigateFallback` if you need to programmatically redirect
to the offline page.

---

## Testing offline behaviour

1. Open DevTools → Network → set throttling to **Offline**
2. Navigate to a cached page — it should load from the SW cache
3. Navigate to an uncached page — the `navigateFallback` page should appear
4. Toggle back to online — `isOffline` should update within ~1 second
