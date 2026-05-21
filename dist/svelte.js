import { derived, readable } from 'svelte/store';
import { page } from '@inertiajs/svelte';

// resources/js/inertia/svelte.ts
function createOnlineStore() {
  return readable(typeof navigator !== "undefined" && navigator.onLine, (set) => {
    const onOnline = () => set(true);
    const onOffline = () => set(false);
    window.addEventListener("online", onOnline);
    window.addEventListener("offline", onOffline);
    return () => {
      window.removeEventListener("online", onOnline);
      window.removeEventListener("offline", onOffline);
    };
  });
}
function usePwa() {
  const isOnline = createOnlineStore();
  const typedPage = page;
  const manifestUrl = derived(typedPage, ($page) => $page.props.pwa?.manifest_url);
  const swInfo = derived(typedPage, ($page) => $page.props.pwa?.sw);
  const navigateFallback = derived(typedPage, ($page) => $page.props.pwa?.navigate_fallback);
  const isOffline = derived(isOnline, ($online) => !$online);
  return { manifestUrl, swInfo, navigateFallback, isOffline };
}

export { usePwa };
//# sourceMappingURL=svelte.js.map
//# sourceMappingURL=svelte.js.map