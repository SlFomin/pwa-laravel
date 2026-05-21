// @vitest-environment jsdom
import { describe, it, expect, vi, beforeEach } from 'vitest';
import { renderHook, act } from '@testing-library/react';
import type { PwaSharedProps } from '../types';

vi.mock('@inertiajs/react', () => ({
    usePage: vi.fn(),
}));

import { usePage } from '@inertiajs/react';
import { usePwa } from '../inertia/react';

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

describe('usePwa (React)', () => {
    it('extracts manifestUrl from page props', () => {
        const { result } = renderHook(() => usePwa());
        expect(result.current.manifestUrl).toBe('/manifest.webmanifest');
    });

    it('extracts swInfo from page props', () => {
        const { result } = renderHook(() => usePwa());
        expect(result.current.swInfo?.url).toBe('/sw.js');
    });

    it('extracts navigateFallback from page props', () => {
        const { result } = renderHook(() => usePwa());
        expect(result.current.navigateFallback).toBe('/');
    });

    it('returns undefined values when pwa props absent', () => {
        mockPage(undefined);
        const { result } = renderHook(() => usePwa());
        expect(result.current.manifestUrl).toBeUndefined();
        expect(result.current.swInfo).toBeUndefined();
    });

    it('isOffline is false when navigator is online', () => {
        Object.defineProperty(navigator, 'onLine', { value: true, configurable: true });
        const { result } = renderHook(() => usePwa());
        expect(result.current.isOffline).toBe(false);
    });

    it('isOffline becomes true when offline event fires', () => {
        Object.defineProperty(navigator, 'onLine', { value: true, configurable: true, writable: true });
        const { result } = renderHook(() => usePwa());

        act(() => {
            Object.defineProperty(navigator, 'onLine', { value: false, configurable: true });
            window.dispatchEvent(new Event('offline'));
        });

        expect(result.current.isOffline).toBe(true);
    });

    it('removes event listeners on unmount', () => {
        const removeSpy = vi.spyOn(window, 'removeEventListener');
        const { unmount } = renderHook(() => usePwa());
        unmount();
        expect(removeSpy).toHaveBeenCalledWith('online', expect.any(Function));
        expect(removeSpy).toHaveBeenCalledWith('offline', expect.any(Function));
        removeSpy.mockRestore();
    });
});
