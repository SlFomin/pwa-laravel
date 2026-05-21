import { VitePWAOptions } from 'vite-plugin-pwa';

interface LaravelPwaOptions extends Partial<VitePWAOptions> {
    /** Enable Inertia mode: sets navigateFallback='/' and excludes API routes */
    inertia?: boolean;
    /** Additional URL patterns to exclude from SW navigation handling */
    excludeFromSW?: (string | RegExp)[];
    /** Override navigate fallback URL. Pass null to disable. */
    navigateFallback?: string | null;
}
interface PwaRegisterOptions {
    onNeedRefresh?: () => void;
    onOfflineReady?: () => void;
    onRegistered?: (reg?: ServiceWorkerRegistration) => void;
    onRegisterError?: (error: unknown) => void;
    immediate?: boolean;
}
interface PwaSharedProps {
    manifest_url: string;
    sw: {
        url: string;
        scope: string;
        register_type: 'autoUpdate' | 'prompt';
        auto_register: boolean;
        available: boolean;
    };
    navigate_fallback: string | null;
    is_ssr: boolean;
}

export type { LaravelPwaOptions as L, PwaRegisterOptions as P, PwaSharedProps as a };
