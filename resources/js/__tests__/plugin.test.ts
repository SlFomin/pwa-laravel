import { describe, it, expect, vi, beforeEach } from 'vitest';

// Mock vite-plugin-pwa so tests don't need Vite's internals
vi.mock('vite-plugin-pwa', () => ({
    VitePWA: vi.fn((options) => ({ _options: options, name: 'vite-plugin-pwa' })),
}));

import { laravelPwa } from '../plugin';
import { VitePWA } from 'vite-plugin-pwa';

const getOptions = () => (VitePWA as ReturnType<typeof vi.fn>).mock.calls.at(-1)?.[0];

beforeEach(() => {
    vi.mocked(VitePWA).mockClear();
});

describe('laravelPwa', () => {
    it('возвращает объект плагина', () => {
        const plugin = laravelPwa();
        expect(plugin).toBeDefined();
        expect(VitePWA).toHaveBeenCalledOnce();
    });

    it('применяет дефолтные параметры Laravel', () => {
        laravelPwa();
        const opts = getOptions();
        expect(opts.registerType).toBe('autoUpdate');
        expect(opts.manifestFilename).toBe('manifest.webmanifest');
        expect(opts.injectRegister).toBeNull();
        expect(opts.workbox.cleanupOutdatedCaches).toBe(true);
        expect(opts.workbox.clientsClaim).toBe(true);
        expect(opts.workbox.skipWaiting).toBe(false);
        expect(opts.devOptions.enabled).toBe(false);
    });

    it('пользовательские опции перезаписывают дефолтные', () => {
        laravelPwa({ registerType: 'prompt', devOptions: { enabled: true } });
        const opts = getOptions();
        expect(opts.registerType).toBe('prompt');
        expect(opts.devOptions.enabled).toBe(true);
    });

    it('в режиме inertia=true устанавливает navigateFallback="/"', () => {
        laravelPwa({ inertia: true });
        const opts = getOptions();
        expect(opts.workbox.navigateFallback).toBe('/');
    });

    it('в режиме inertia=false не устанавливает navigateFallback', () => {
        laravelPwa({ inertia: false });
        const opts = getOptions();
        expect(opts.workbox.navigateFallback).toBeUndefined();
    });

    it('явный navigateFallback перезаписывает inertia-дефолт', () => {
        laravelPwa({ inertia: true, navigateFallback: '/app' });
        const opts = getOptions();
        expect(opts.workbox.navigateFallback).toBe('/app');
    });

    it('navigateFallback=null отключает fallback даже в inertia-режиме', () => {
        laravelPwa({ inertia: true, navigateFallback: null });
        const opts = getOptions();
        expect(opts.workbox.navigateFallback).toBeUndefined();
    });

    it('в режиме inertia=true добавляет стандартные denylist-паттерны', () => {
        laravelPwa({ inertia: true });
        const opts = getOptions();
        const denylist: RegExp[] = opts.workbox.navigateFallbackDenylist;
        expect(denylist).toBeDefined();
        expect(denylist.some((r) => r.test('/api/users'))).toBe(true);
        expect(denylist.some((r) => r.test('/sanctum/csrf-cookie'))).toBe(true);
        expect(denylist.some((r) => r.test('/livewire/update'))).toBe(true);
    });

    it('в режиме inertia=false denylist не добавляется', () => {
        laravelPwa({ inertia: false });
        const opts = getOptions();
        expect(opts.workbox.navigateFallbackDenylist).toBeUndefined();
    });

    it('пользовательский excludeFromSW добавляется в denylist (строка)', () => {
        laravelPwa({ inertia: true, excludeFromSW: ['/admin/'] });
        const opts = getOptions();
        const denylist: RegExp[] = opts.workbox.navigateFallbackDenylist;
        expect(denylist.some((r) => r.test('/admin/dashboard'))).toBe(true);
    });

    it('пользовательский excludeFromSW добавляется в denylist (RegExp)', () => {
        laravelPwa({ inertia: true, excludeFromSW: [/^\/private\//] });
        const opts = getOptions();
        const denylist: RegExp[] = opts.workbox.navigateFallbackDenylist;
        expect(denylist.some((r) => r.test('/private/data'))).toBe(true);
    });

    it('workbox-опции пользователя мержатся с дефолтными', () => {
        laravelPwa({ workbox: { skipWaiting: true, navigateFallback: '/fallback' } });
        const opts = getOptions();
        expect(opts.workbox.skipWaiting).toBe(true);
        expect(opts.workbox.cleanupOutdatedCaches).toBe(true); // из дефолтов
        expect(opts.workbox.navigateFallback).toBe('/fallback');
    });
});