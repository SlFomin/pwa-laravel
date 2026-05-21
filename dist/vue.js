import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

// resources/js/inertia/vue.ts
function usePwa() {
  const page = usePage();
  const manifestUrl = computed(() => page.props.pwa?.manifest_url);
  const swInfo = computed(() => page.props.pwa?.sw);
  const navigateFallback = computed(() => page.props.pwa?.navigate_fallback);
  const isOffline = computed(() => typeof navigator !== "undefined" && !navigator.onLine);
  return { manifestUrl, swInfo, navigateFallback, isOffline };
}

export { usePwa };
//# sourceMappingURL=vue.js.map
//# sourceMappingURL=vue.js.map