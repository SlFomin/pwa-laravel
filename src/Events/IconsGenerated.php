<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Events;

final class IconsGenerated
{
    /**
     * @param  list<array{src: string, sizes: string, type: string, purpose?: string}>  $icons
     */
    public function __construct(
        public readonly string $sourcePath,
        public readonly string $outputPath,
        public readonly array $icons,
    ) {}
}
