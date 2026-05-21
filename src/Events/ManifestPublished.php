<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Events;

use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

final class ManifestPublished
{
    public function __construct(
        public readonly string $path,
        public readonly ManifestBuilder $manifest,
        public readonly int $bytes,
    ) {}
}
