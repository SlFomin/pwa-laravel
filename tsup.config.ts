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