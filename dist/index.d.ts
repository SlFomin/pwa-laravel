import { Plugin } from 'vite';
import { L as LaravelPwaOptions, P as PwaRegisterOptions } from './types-CmVI4Ka9.js';
export { a as PwaSharedProps } from './types-CmVI4Ka9.js';
import 'vite-plugin-pwa';

declare function laravelPwa(options?: LaravelPwaOptions): Plugin | Plugin[];

/**
 * Programmatic SW registration via vite-plugin-pwa virtual module.
 * Use when auto_register is disabled in config and you need manual control.
 */
declare function setupPwa(options?: PwaRegisterOptions): Promise<{
    updateSW: (reloadPage?: boolean) => Promise<void>;
}>;

export { LaravelPwaOptions, PwaRegisterOptions, laravelPwa, setupPwa as registerSW, setupPwa };
