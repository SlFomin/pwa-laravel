import { usePage } from '@inertiajs/react';
import { useMemo, useSyncExternalStore } from 'react';
import type { PwaSharedProps } from '../types';

function subscribeOnline(callback: () => void): () => void {
    window.addEventListener('online', callback);
    window.addEventListener('offline', callback);
    return () => {
        window.removeEventListener('online', callback);
        window.removeEventListener('offline', callback);
    };
}

function getOnlineSnapshot(): boolean {
    return typeof navigator !== 'undefined' && navigator.onLine;
}

function getServerSnapshot(): boolean {
    return true;
}

export interface UsePwaReturn {
    manifestUrl: string | undefined;
    swInfo: PwaSharedProps['sw'] | undefined;
    navigateFallback: string | null | undefined;
    isOffline: boolean;
}

export function usePwa(): UsePwaReturn {
    const { props } = usePage<{ pwa?: PwaSharedProps }>();
    const pwa = props.pwa;

    const isOnline = useSyncExternalStore(subscribeOnline, getOnlineSnapshot, getServerSnapshot);

    return useMemo(
        () => ({
            manifestUrl: pwa?.manifest_url,
            swInfo: pwa?.sw,
            navigateFallback: pwa?.navigate_fallback,
            isOffline: !isOnline,
        }),
        [pwa, isOnline],
    );
}