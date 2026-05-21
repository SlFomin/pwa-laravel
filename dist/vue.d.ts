import { ComputedRef } from 'vue';
import { a as PwaSharedProps } from './types-CmVI4Ka9.js';
import 'vite-plugin-pwa';

interface UsePwaReturn {
    manifestUrl: ComputedRef<string | undefined>;
    swInfo: ComputedRef<PwaSharedProps['sw'] | undefined>;
    navigateFallback: ComputedRef<string | null | undefined>;
    isOffline: ComputedRef<boolean>;
}
declare function usePwa(): UsePwaReturn;

export { type UsePwaReturn, usePwa };
