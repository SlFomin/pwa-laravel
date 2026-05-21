import { defineConfig } from 'vitest/config';

export default defineConfig({
    test: {
        globals: true,
        environment: 'node',
        include: ['resources/js/__tests__/**/*.test.ts'],
    },
});