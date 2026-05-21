// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest';
import type { PwaSharedProps } from '../types';

vi.mock('@inertiajs/vue3', () => ({
    usePage: vi.fn(),
}));

import { usePage } from '@inertiajs/vue3';
import { usePwa } from '../inertia/vue';

const mockPwaProps: PwaSharedProps = {
    manifest_url: '/manifest.webmanifest',
    sw: { url: '/sw.js', scope: '/', register_type: 'autoUpdate', auto_register: true, available: true },
    navigate_fallback: '/',
    is_ssr: false,
};

function mockPage(pwa?: PwaSharedProps) {
    vi.mocked(usePage).mockReturnValue({ props: { pwa } } as ReturnType<typeof usePage>);
}

beforeEach(() => {
    mockPage(mockPwaProps);
    Object.defineProperty(navigator, 'onLine', { value: true, configurable: true, writable: true });
});

describe('usePwa (Vue)', () => {
    it('extracts manifestUrl from page props', () => {
        const { manifestUrl } = usePwa();
        expect(manifestUrl.value).toBe('/manifest.webmanifest');
    });

    it('extracts swInfo from page props', () => {
        const { swInfo } = usePwa();
        expect(swInfo.value?.url).toBe('/sw.js');
    });

    it('extracts navigateFallback from page props', () => {
        const { navigateFallback } = usePwa();
        expect(navigateFallback.value).toBe('/');
    });

    it('returns undefined values when pwa props absent', () => {
        mockPage(undefined);
        const { manifestUrl, swInfo, navigateFallback } = usePwa();
        expect(manifestUrl.value).toBeUndefined();
        expect(swInfo.value).toBeUndefined();
        expect(navigateFallback.value).toBeUndefined();
    });

    it('isOffline is false when navigator.onLine is true', () => {
        Object.defineProperty(navigator, 'onLine', { value: true, configurable: true });
        const { isOffline } = usePwa();
        expect(isOffline.value).toBe(false);
    });

    it('isOffline is true when navigator.onLine is false', () => {
        Object.defineProperty(navigator, 'onLine', { value: false, configurable: true });
        const { isOffline } = usePwa();
        expect(isOffline.value).toBe(true);
    });
});
