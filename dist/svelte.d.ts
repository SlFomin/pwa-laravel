import { Readable } from 'svelte/store';
import { a as PwaSharedProps } from './types-CmVI4Ka9.js';
import 'vite-plugin-pwa';

interface UsePwaReturn {
    manifestUrl: Readable<string | undefined>;
    swInfo: Readable<PwaSharedProps['sw'] | undefined>;
    navigateFallback: Readable<string | null | undefined>;
    isOffline: Readable<boolean>;
}
declare function usePwa(): UsePwaReturn;

export { type UsePwaReturn, usePwa };
