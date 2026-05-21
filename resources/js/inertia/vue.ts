import { computed, type ComputedRef } from 'vue';
import { usePage } from '@inertiajs/vue3';
import type { PwaSharedProps } from '../types';

export interface UsePwaReturn {
    manifestUrl: ComputedRef<string | undefined>;
    swInfo: ComputedRef<PwaSharedProps['sw'] | undefined>;
    navigateFallback: ComputedRef<string | null | undefined>;
    isOffline: ComputedRef<boolean>;
}

export function usePwa(): UsePwaReturn {
    const page = usePage<{ pwa?: PwaSharedProps }>();

    const manifestUrl = computed(() => page.props.pwa?.manifest_url);
    const swInfo = computed(() => page.props.pwa?.sw);
    const navigateFallback = computed(() => page.props.pwa?.navigate_fallback);
    const isOffline = computed(() => typeof navigator !== 'undefined' && !navigator.onLine);

    return { manifestUrl, swInfo, navigateFallback, isOffline };
}