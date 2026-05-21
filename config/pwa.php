<?php

declare(strict_types=1);
use SlFomin\PwaLaravel\Manifest\Resolvers\DefaultManifestResolver;

return [

    /*
    |--------------------------------------------------------------------------
    | Manifest
    |--------------------------------------------------------------------------
    */
    'manifest' => [
        // 'static' — файл из public/, собранный Vite
        // 'dynamic' — генерируется Laravel-контроллером на лету
        'driver' => env('PWA_MANIFEST_DRIVER', 'static'),

        // URL, по которому браузер запрашивает манифест
        'route' => env('PWA_MANIFEST_ROUTE', '/manifest.webmanifest'),

        // Для статичного режима — путь к файлу на диске.
        // Переопределите через PWA_STATIC_MANIFEST_PATH, если сборка и runtime
        // выполняются на разных машинах (иначе путь запечётся в config:cache).
        'static_path' => env('PWA_STATIC_MANIFEST_PATH', public_path('build/manifest.webmanifest')),

        // Для динамичного режима
        'dynamic' => [
            'resolver' => DefaultManifestResolver::class,
            'cache' => env('PWA_MANIFEST_CACHE', true),
            'cache_ttl' => env('PWA_MANIFEST_CACHE_TTL', 3600),
            'cache_key_prefix' => 'pwa.manifest.',
            'cache_store' => env('PWA_MANIFEST_CACHE_STORE'), // null = default
        ],

        // Дефолтные данные манифеста (basis для динамичного режима
        // и fallback для статичного, если Vite ещё не собран)
        'data' => [
            'name' => env('APP_NAME', 'Laravel'),
            'short_name' => env('PWA_SHORT_NAME', env('APP_NAME', 'Laravel')),
            'description' => env('PWA_DESCRIPTION', ''),
            'start_url' => '/',
            'scope' => '/',
            'display' => env('PWA_DISPLAY', 'standalone'),
            'orientation' => 'any',
            'background_color' => env('PWA_BG_COLOR', '#ffffff'),
            'theme_color' => env('PWA_THEME_COLOR', '#000000'),
            'lang' => env('APP_LOCALE', 'en'),
            'dir' => 'ltr',
            'categories' => [],
            'icons' => [],
            'shortcuts' => [],
            'screenshots' => [],
            'related_applications' => [],
            'prefer_related_applications' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Icons
    |--------------------------------------------------------------------------
    */
    'icons' => [
        // Переопределите через env, если build и runtime на разных машинах (config:cache).
        'source' => env('PWA_ICON_SOURCE', resource_path('images/pwa-icon.png')),
        'output_path' => env('PWA_ICON_OUTPUT_PATH', public_path('icons')),
        'output_url_prefix' => '/icons',
        'sizes' => [72, 96, 128, 144, 152, 192, 384, 512],
        'generate_maskable' => true,
        'maskable_sizes' => [192, 512],
        'maskable_padding' => 0.1,
        'maskable_background' => null, // null = из manifest.background_color
        'generate_apple_touch' => true,
        'apple_touch_size' => 180,
        'generate_favicon' => true,
        'favicon_sizes' => [16, 32],
        'format' => 'png',
        'quality' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Worker
    |--------------------------------------------------------------------------
    */
    'service_worker' => [
        // 'generateSW' | 'injectManifest'
        'strategy' => env('PWA_SW_STRATEGY', 'generateSW'),
        'url' => env('PWA_SW_URL', '/sw.js'),
        'scope' => '/',
        'auto_register' => true,
        'register_type' => 'autoUpdate',
        'dev_enabled' => env('PWA_SW_DEV', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Inertia
    |--------------------------------------------------------------------------
    */
    'inertia' => [
        'auto_detect' => true,
        'share_props' => true,
        'shared_prop_key' => 'pwa',
        'navigate_fallback' => '/',
        'exclude_from_sw' => [
            '/api/*',
            '/sanctum/*',
            '/broadcasting/*',
            '/livewire/*',
            '/horizon/*',
            '/telescope/*',
            '/pulse/*',
        ],
        'ssr_enabled' => env('PWA_INERTIA_SSR', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'manifest' => [
            'Content-Type' => 'application/manifest+json',
            'Cache-Control' => 'public, max-age=3600',
        ],
        'service_worker' => [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Service-Worker-Allowed' => '/',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Vite integration
    |--------------------------------------------------------------------------
    */
    'vite' => [
        // Переопределите через env, если build и runtime на разных машинах (config:cache).
        'manifest_path' => env('PWA_VITE_MANIFEST_PATH', public_path('build/manifest.json')),
        'build_path' => env('PWA_VITE_BUILD_PATH', public_path('build')),
        'base_url' => '/build/',
    ],

];
