import { defineConfig } from 'tsup';

export default defineConfig({
    entry: {
        index: 'resources/js/index.ts',
        vue: 'resources/js/inertia/vue.ts',
        react: 'resources/js/inertia/react.ts',
        svelte: 'resources/js/inertia/svelte.ts',
    },
    format: ['esm'],
    dts: true,
    sourcemap: true,
    // Disable code splitting so each entry is self-contained — no hashed
    // chunk filenames in dist/ (important for vendor path imports).
    splitting: false,
    clean: true,
    external: [
        'vite',
        'vite-plugin-pwa',
        'virtual:pwa-register',
        '@inertiajs/vue3',
        '@inertiajs/react',
        '@inertiajs/svelte',
        'vue',
        'react',
        'svelte',
        'svelte/store',
    ],
    treeshake: true,
    outDir: 'dist',
});