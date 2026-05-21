import { describe, it, expect, vi } from 'vitest';

vi.mock('vite-plugin-pwa', () => ({
    VitePWA: vi.fn((options) => ({ _options: options, name: 'vite-plugin-pwa' })),
}));

import * as pkg from '../index';

describe('публичный API пакета', () => {
    it('экспортирует laravelPwa', () => {
        expect(typeof pkg.laravelPwa).toBe('function');
    });

    it('экспортирует setupPwa', () => {
        expect(typeof pkg.setupPwa).toBe('function');
    });

    it('экспортирует registerSW как псевдоним setupPwa', () => {
        expect(pkg.registerSW).toBe(pkg.setupPwa);
    });
});