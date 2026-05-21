import type { RegisterSWOptions } from 'virtual:pwa-register';
import type { PwaRegisterOptions } from './types';

/**
 * Programmatic SW registration via vite-plugin-pwa virtual module.
 * Use when auto_register is disabled in config and you need manual control.
 */
export async function setupPwa(options: PwaRegisterOptions = {}): Promise<{ updateSW: (reloadPage?: boolean) => Promise<void> }> {
    // Dynamic import keeps this SSR-safe and avoids bundling the virtual module
    const { registerSW } = await import('virtual:pwa-register');

    const swOptions: RegisterSWOptions = {
        immediate: options.immediate ?? true,
        onRegisteredSW(_url: string, registration: ServiceWorkerRegistration | undefined) {
            options.onRegistered?.(registration);
        },
    };

    if (options.onNeedRefresh) {
        swOptions.onNeedRefresh = options.onNeedRefresh;
    }
    if (options.onOfflineReady) {
        swOptions.onOfflineReady = options.onOfflineReady;
    }
    if (options.onRegisterError) {
        swOptions.onRegisterError = options.onRegisterError;
    }

    const updateSW = registerSW(swOptions);

    return { updateSW };
}

export { setupPwa as registerSW };
