<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Core\Shortcuts;

/**
 * Probes an icon resource to determine its dimensions and MIME type.
 *
 * Implementations may read from the filesystem (default in Laravel bridge),
 * but MUST NOT make HTTP requests — probing should be deterministic and fast,
 * suitable for use during manifest generation.
 *
 * Returns null when the resource cannot be located or its metadata cannot be
 * determined (e.g. remote URL, broken file, unsupported format).
 */
interface IconMetadataProbe
{
    public function probe(string $src): ?IconMetadata;
}
