import { usePage } from '@inertiajs/react';
import { useSyncExternalStore, useMemo } from 'react';

// resources/js/inertia/react.ts
function subscribeOnline(callback) {
  window.addEventListener("online", callback);
  window.addEventListener("offline", callback);
  return () => {
    window.removeEventListener("online", callback);
    window.removeEventListener("offline", callback);
  };
}
function getOnlineSnapshot() {
  return typeof navigator !== "undefined" && navigator.onLine;
}
function getServerSnapshot() {
  return true;
}
function usePwa() {
  const { props } = usePage();
  const pwa = props.pwa;
  const isOnline = useSyncExternalStore(subscribeOnline, getOnlineSnapshot, getServerSnapshot);
  return useMemo(
    () => ({
      manifestUrl: pwa?.manifest_url,
      swInfo: pwa?.sw,
      navigateFallback: pwa?.navigate_fallback,
      isOffline: !isOnline
    }),
    [pwa, isOnline]
  );
}

export { usePwa };
//# sourceMappingURL=react.js.map
//# sourceMappingURL=react.js.map