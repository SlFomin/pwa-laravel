<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Contracts;

use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

interface ManifestDriver
{
    /**
     * Resolve the manifest for the given request.
     *
     * Must never return null — fall back to default config data when the
     * underlying source (file, database, etc.) is unavailable.
     * The returned builder is not yet validated; call validate() before serialising.
     */
    public function resolve(Request $request): ManifestBuilder;

    /**
     * Canonical public URL of the manifest for the given request.
     *
     * May vary per tenant or locale (e.g. "/en/manifest.webmanifest").
     * Must be deterministic for the same request so it can be shared via Inertia props.
     */
    public function url(Request $request): string;

    /**
     * HTML attributes for the <link rel="manifest"> tag.
     *
     * Must include at least "rel" and "href". Dynamic drivers may add "crossorigin".
     *
     * @return array<string, string>
     */
    public function linkAttributes(Request $request): array;
}
