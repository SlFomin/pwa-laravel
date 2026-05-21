import { readable, derived, type Readable } from 'svelte/store';
import { page } from '@inertiajs/svelte';
import type { Page } from '@inertiajs/core';
import type { PwaSharedProps } from '../types';

export interface UsePwaReturn {
    manifestUrl: Readable<string | undefined>;
    swInfo: Readable<PwaSharedProps['sw'] | undefined>;
    navigateFallback: Readable<string | null | undefined>;
    isOffline: Readable<boolean>;
}

type PageWithPwa = Page<{ pwa?: PwaSharedProps }>;

function createOnlineStore(): Readable<boolean> {
    return readable(typeof navigator !== 'undefined' && navigator.onLine, (set) => {
        const onOnline = () => set(true);
        const onOffline = () => set(false);
        window.addEventListener('online', onOnline);
        window.addEventListener('offline', onOffline);
        return () => {
            window.removeEventListener('online', onOnline);
            window.removeEventListener('offline', onOffline);
        };
    });
}

export function usePwa(): UsePwaReturn {
    const isOnline = createOnlineStore();

    // page from @inertiajs/svelte is a Readable<Page<PageProps>>
    const typedPage = page as unknown as Readable<PageWithPwa>;

    const manifestUrl = derived(typedPage, ($page) => $page.props.pwa?.manifest_url);
    const swInfo = derived(typedPage, ($page) => $page.props.pwa?.sw);
    const navigateFallback = derived(typedPage, ($page) => $page.props.pwa?.navigate_fallback);
    const isOffline = derived(isOnline, ($online) => !$online);

    return { manifestUrl, swInfo, navigateFallback, isOffline };
}