<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Manifest\Resolvers;

use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Contracts\ManifestResolver;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

final class LocaleManifestResolver implements ManifestResolver
{
    /**
     * @param  array<string, array<string, mixed>>  $translations
     */
    public function __construct(
        protected readonly array $translations = [],
    ) {}

    public function resolve(Request $request, ManifestBuilder $default): ManifestBuilder
    {
        $locale = app()->getLocale();
        $translated = $this->translations[$locale] ?? [];

        return $default
            ->lang($locale)
            ->merge($translated);
    }

    public function cacheKey(Request $request): ?string
    {
        return 'locale.'.app()->getLocale();
    }
}
