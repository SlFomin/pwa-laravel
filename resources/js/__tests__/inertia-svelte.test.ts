// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { get } from 'svelte/store';
import type { PwaSharedProps } from '../types';

// vi.hoisted ensures this runs before vi.mock factory (which is hoisted to top of file)
const { mockPageStore } = vi.hoisted(() => {
    type Subscriber<T> = (value: T) => void;

    let value: any = { component: '', props: {}, url: '/', version: null, clearHistory: false, encryptHistory: false, rememberedState: {}, scrollRegions: [], deferredProps: {}, mergeProps: [], resolvedErrors: {} };
    let subscribers: Subscriber<any>[] = [];

    const store = {
        subscribe(fn: Subscriber<any>) {
            subscribers.push(fn);
            fn(value);
            return () => { subscribers = subscribers.filter((s) => s !== fn); };
        },
        set(v: any) { value = v; subscribers.forEach((s) => s(value)); },
        update(fn: (v: any) => any) { store.set(fn(value)); },
    };

    return { mockPageStore: store };
});

vi.mock('@inertiajs/svelte', () => ({ page: mockPageStore }));

import { usePwa } from '../inertia/svelte';

const mockPwaProps: PwaSharedProps = {
    manifest_url: '/manifest.webmanifest',
    sw: { url: '/sw.js', scope: '/', register_type: 'autoUpdate', auto_register: true, available: true },
    navigate_fallback: '/',
    is_ssr: false,
};

const emptyPage = { component: '', props: {}, url: '/', version: null, clearHistory: false, encryptHistory: false, rememberedState: {}, scrollRegions: [], deferredProps: {}, mergeProps: [], resolvedErrors: {} };

beforeEach(() => {
    mockPageStore.set({ ...emptyPage, props: { pwa: mockPwaProps } });
    Object.defineProperty(navigator, 'onLine', { value: true, configurable: true, writable: true });
});

describe('usePwa (Svelte)', () => {
    it('extracts manifestUrl from page store', () => {
        const { manifestUrl } = usePwa();
        expect(get(manifestUrl)).toBe('/manifest.webmanifest');
    });

    it('extracts swInfo from page store', () => {
        const { swInfo } = usePwa();
        expect(get(swInfo)?.url).toBe('/sw.js');
    });

    it('extracts navigateFallback from page store', () => {
        const { navigateFallback } = usePwa();
        expect(get(navigateFallback)).toBe('/');
    });

    it('returns undefined when pwa props absent', () => {
        mockPageStore.set({ ...emptyPage, props: {} });
        const { manifestUrl } = usePwa();
        expect(get(manifestUrl)).toBeUndefined();
    });

    it('isOffline is false when navigator is online', () => {
        Object.defineProperty(navigator, 'onLine', { value: true, configurable: true });
        const { isOffline } = usePwa();
        expect(get(isOffline)).toBe(false);
    });

    it('isOffline becomes true when offline event fires', () => {
        Object.defineProperty(navigator, 'onLine', { value: true, configurable: true, writable: true });
        const { isOffline } = usePwa();

        let current = get(isOffline);
        const unsubscribe = isOffline.subscribe((v) => { current = v; });

        Object.defineProperty(navigator, 'onLine', { value: false, configurable: true });
        window.dispatchEvent(new Event('offline'));

        expect(current).toBe(true);
        unsubscribe();
    });

    it('store cleanup removes event listeners', () => {
        const removeSpy = vi.spyOn(window, 'removeEventListener');
        const { isOffline } = usePwa();

        const unsubscribe = isOffline.subscribe(() => {});
        unsubscribe();

        expect(removeSpy).toHaveBeenCalledWith('online', expect.any(Function));
        expect(removeSpy).toHaveBeenCalledWith('offline', expect.any(Function));
        removeSpy.mockRestore();
    });
});
