<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Core\Shortcuts;

/**
 * Probes icon metadata by reading files from the application's `public/` directory.
 *
 * - PNG/JPG/WEBP/etc: uses native `getimagesize()`.
 * - SVG: reports `sizes: "any"` and `type: "image/svg+xml"` without parsing
 *        viewBox (per PWA best practice — scalable icons should declare "any").
 * - Remote URLs (http://, https://): returns null (no network requests).
 * - Missing files: returns null.
 * - Unsupported formats: returns null.
 */
final class FilesystemIconMetadataProbe implements IconMetadataProbe
{
    public function __construct(
        private readonly string $publicPath,
    ) {}

    public function probe(string $src): ?IconMetadata
    {
        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            return null;
        }

        $relative = ltrim($src, '/');
        $absolute = rtrim($this->publicPath, '/').'/'.$relative;

        if (! is_file($absolute) || ! is_readable($absolute)) {
            return null;
        }

        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        if ($extension === 'svg') {
            return new IconMetadata(sizes: 'any', type: 'image/svg+xml');
        }

        $info = @getimagesize($absolute);
        if ($info === false) {
            return null;
        }

        return new IconMetadata(
            sizes: "{$info[0]}x{$info[1]}",
            type: $info['mime'],
        );
    }
}
