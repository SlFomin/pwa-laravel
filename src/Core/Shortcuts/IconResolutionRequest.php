<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Core\Shortcuts;

/**
 * Carries the user-declared icon specification through the resolution pipeline.
 *
 * One of `iconString`, `iconObject`, `iconsArray`, `iconSetName` is set; others
 * are null. This precondition is enforced by the attribute constructor before
 * a request is built.
 */
final readonly class IconResolutionRequest
{
    /**
     * @param  list<ShortcutIcon>|null  $iconsArray
     */
    public function __construct(
        public ?string $iconString = null,
        public ?ShortcutIcon $iconObject = null,
        public ?array $iconsArray = null,
        public ?string $iconSetName = null,
        public ?string $sizesHint = null,
        public ?string $typeHint = null,
        public ?string $sourceClass = null,
    ) {}
}
