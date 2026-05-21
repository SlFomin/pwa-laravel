# Update Prompt

When a new version of your app is deployed, the service worker detects the change and
the `onNeedRefresh` callback fires. This guide shows how to show an "Update available" toast
in Vue, React, and Svelte.

---

## Vue

```vue
<script setup lang="ts">
import { ref } from 'vue';
import { useRegisterSW } from 'virtual:pwa-register/vue';

const showUpdate = ref(false);

const { updateServiceWorker } = useRegisterSW({
    onNeedRefresh() {
        showUpdate.value = true;
    },
    onOfflineReady() {
        console.log('[PWA] Ready to work offline');
    },
});
</script>

<template>
    <div v-if="showUpdate" class="update-banner">
        New version available.
        <button @click="updateServiceWorker()">Reload</button>
        <button @click="showUpdate = false">Later</button>
    </div>
</template>
```

---

## React

```tsx
import { useRegisterSW } from 'virtual:pwa-register/react';

export function UpdateBanner() {
    const {
        needRefresh: [needRefresh, setNeedRefresh],
        updateServiceWorker,
    } = useRegisterSW({
        onOfflineReady() {
            console.log('[PWA] Ready to work offline');
        },
    });

    if (!needRefresh) return null;

    return (
        <div className="update-banner">
            New version available.
            <button onClick={() => updateServiceWorker(true)}>Reload</button>
            <button onClick={() => setNeedRefresh(false)}>Later</button>
        </div>
    );
}
```

---

## Svelte

```svelte
<script lang="ts">
import { useRegisterSW } from 'virtual:pwa-register/svelte';

const { needRefresh, updateServiceWorker } = useRegisterSW({
    onOfflineReady() {
        console.log('[PWA] Ready to work offline');
    },
});
</script>

{#if $needRefresh}
    <div class="update-banner">
        New version available.
        <button on:click={() => updateServiceWorker(true)}>Reload</button>
        <button on:click={() => needRefresh.set(false)}>Later</button>
    </div>
{/if}
```

---

## Registration type

The update flow above requires `registerType: 'prompt'` in `laravelPwa()`:

```ts
// vite.config.ts
laravelPwa({
    registerType: 'prompt',   // ← enables onNeedRefresh callback
})
```

With the default `registerType: 'autoUpdate'`, the service worker skips waiting and reloads
automatically without user interaction. Use `'prompt'` when you need explicit user consent.

---

## Inertia: forcing a full reload

After `updateServiceWorker()`, Inertia's SPA state is stale. Force a full page reload:

```ts
updateServiceWorker().then(() => {
    window.location.reload();
});
```
