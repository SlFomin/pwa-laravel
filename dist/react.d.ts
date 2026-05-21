import { a as PwaSharedProps } from './types-CmVI4Ka9.js';
import 'vite-plugin-pwa';

interface UsePwaReturn {
    manifestUrl: string | undefined;
    swInfo: PwaSharedProps['sw'] | undefined;
    navigateFallback: string | null | undefined;
    isOffline: boolean;
}
declare function usePwa(): UsePwaReturn;

export { type UsePwaReturn, usePwa };
