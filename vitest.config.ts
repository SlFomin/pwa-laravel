import { defineConfig } from 'vitest/config';

export default defineConfig({
    resolve: {
        conditions: ['svelte', 'browser'],
    },
    test: {
        globals: true,
        environment: 'node',
        include: ['resources/js/__tests__/**/*.test.{ts,tsx}'],
    },
});