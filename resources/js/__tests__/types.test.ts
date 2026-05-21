import { describe, it, expectTypeOf } from 'vitest';
import type { LaravelPwaOptions, PwaRegisterOptions, PwaSharedProps } from '../types';

describe('типы пакета', () => {
    it('LaravelPwaOptions содержит поле inertia: boolean', () => {
        expectTypeOf<LaravelPwaOptions['inertia']>().toEqualTypeOf<boolean | undefined>();
    });

    it('LaravelPwaOptions содержит excludeFromSW', () => {
        expectTypeOf<LaravelPwaOptions['excludeFromSW']>().toEqualTypeOf<
            (string | RegExp)[] | undefined
        >();
    });

    it('PwaRegisterOptions: все поля опциональны', () => {
        const opts: PwaRegisterOptions = {};
        expect(opts).toBeDefined();
    });

    it('PwaSharedProps имеет корректную структуру sw', () => {
        expectTypeOf<PwaSharedProps['sw']['register_type']>().toEqualTypeOf<
            'autoUpdate' | 'prompt'
        >();
    });
});