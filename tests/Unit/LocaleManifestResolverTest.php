<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;
use SlFomin\PwaLaravel\Manifest\Resolvers\LocaleManifestResolver;

it('returns default builder unchanged when no translations defined', function (): void {
    $resolver = new LocaleManifestResolver([]);
    $default = ManifestBuilder::make(['name' => 'App', 'short_name' => 'App', 'start_url' => '/', 'display' => 'standalone']);

    app()->setLocale('en');
    $result = $resolver->resolve(Request::create('/'), $default);

    expect($result->get('name'))->toBe('App');
});

it('returns default builder when locale has no translation', function (): void {
    $resolver = new LocaleManifestResolver(['de' => ['name' => 'Deutsche App']]);
    $default = ManifestBuilder::make(['name' => 'App', 'short_name' => 'App', 'start_url' => '/', 'display' => 'standalone']);

    app()->setLocale('fr');
    $result = $resolver->resolve(Request::create('/'), $default);

    expect($result->get('name'))->toBe('App');
});

it('applies translation for current locale', function (): void {
    $resolver = new LocaleManifestResolver([
        'ru' => ['name' => 'Приложение', 'short_name' => 'Прил'],
    ]);
    $default = ManifestBuilder::make(['name' => 'App', 'short_name' => 'App', 'start_url' => '/', 'display' => 'standalone']);

    app()->setLocale('ru');
    $result = $resolver->resolve(Request::create('/'), $default);

    expect($result->get('name'))->toBe('Приложение')
        ->and($result->get('short_name'))->toBe('Прил');
});

it('sets lang field from current locale', function (): void {
    $resolver = new LocaleManifestResolver([]);
    $default = ManifestBuilder::make(['name' => 'App', 'short_name' => 'App', 'start_url' => '/', 'display' => 'standalone']);

    app()->setLocale('de');
    $result = $resolver->resolve(Request::create('/'), $default);

    expect($result->get('lang'))->toBe('de');
});

it('cache key differs between locales', function (): void {
    $resolver = new LocaleManifestResolver([]);
    $request = Request::create('/');

    app()->setLocale('en');
    $keyEn = $resolver->cacheKey($request);

    app()->setLocale('ru');
    $keyRu = $resolver->cacheKey($request);

    expect($keyEn)->not->toBe($keyRu)
        ->and($keyEn)->toBe('locale.en')
        ->and($keyRu)->toBe('locale.ru');
});

it('translation merges into default without removing existing fields', function (): void {
    $resolver = new LocaleManifestResolver([
        'es' => ['name' => 'Aplicación'],
    ]);
    $default = ManifestBuilder::make([
        'name' => 'App',
        'short_name' => 'App',
        'start_url' => '/',
        'display' => 'standalone',
        'theme_color' => '#000000',
    ]);

    app()->setLocale('es');
    $result = $resolver->resolve(Request::create('/'), $default);

    expect($result->get('name'))->toBe('Aplicación')
        ->and($result->get('theme_color'))->toBe('#000000');
});
