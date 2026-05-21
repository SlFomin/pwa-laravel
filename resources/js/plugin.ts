import type { Plugin } from 'vite';
import { VitePWA, type VitePWAOptions } from 'vite-plugin-pwa';
import type { LaravelPwaOptions } from './types';

const LARAVEL_DEFAULTS: Partial<VitePWAOptions> = {
    registerType: 'autoUpdate',
    manifestFilename: 'manifest.webmanifest',
    // Регистрацию SW берёт на себя blade-директива @pwaRegisterSW
    injectRegister: null,
    workbox: {
        cleanupOutdatedCaches: true,
        clientsClaim: true,
        skipWaiting: false,
    },
    devOptions: {
        enabled: false,
    },
};

const DEFAULT_INERTIA_EXCLUDES: RegExp[] = [
    /^\/api\//,
    /^\/sanctum\//,
    /^\/broadcasting\//,
    /^\/livewire\//,
    /^\/horizon\//,
    /^\/telescope\//,
    /^\/pulse\//,
];

export function laravelPwa(options: LaravelPwaOptions = {}): Plugin {
    const {
        inertia = false,
        excludeFromSW = [],
        navigateFallback,
        ...vitePwaOptions
    } = options;

    const denylist: RegExp[] = [
        ...(inertia ? DEFAULT_INERTIA_EXCLUDES : []),
        ...excludeFromSW.map((p) => (p instanceof RegExp ? p : new RegExp(p))),
        ...(vitePwaOptions.workbox?.navigateFallbackDenylist ?? []),
    ];

    const resolvedFallback =
        navigateFallback !== undefined
            ? navigateFallback
            : inertia
              ? '/'
              : (vitePwaOptions.workbox?.navigateFallback ?? null);

    const merged: Partial<VitePWAOptions> = {
        ...LARAVEL_DEFAULTS,
        ...vitePwaOptions,
        workbox: {
            ...LARAVEL_DEFAULTS.workbox,
            ...vitePwaOptions.workbox,
            ...(resolvedFallback !== null ? { navigateFallback: resolvedFallback } : {}),
            ...(denylist.length > 0 ? { navigateFallbackDenylist: denylist } : {}),
        },
    };

    return VitePWA(merged) as unknown as Plugin;
}