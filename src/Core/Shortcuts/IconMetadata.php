<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Core\Shortcuts;

final readonly class IconMetadata
{
    public function __construct(
        public string $sizes,
        public ?string $type = null,
    ) {}
}
