<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Core\Shortcuts;

/**
 * Default icon resolution strategy.
 *
 * Handles three icon-declaration forms:
 *
 * 1. String shorthand — wraps in a single ShortcutIcon, auto-probes metadata
 *    via {@see IconMetadataProbe} when sizes/type are not explicitly provided.
 *
 * 2. ShortcutIcon instance — wraps in a single-element array; auto-probe fills
 *    missing sizes/type without overwriting user-provided values.
 *
 * 3. Explicit array — passthrough with auto-probe enrichment per entry.
 *
 * Empty input (no icon declared) returns an empty array.
 *
 * In v1.3 this class is extended with icon-set resolution.
 */
final class DefaultIconResolver implements IconResolver
{
    public function __construct(
        private readonly IconMetadataProbe $probe,
        private readonly IconSetRegistry $iconSetRegistry,
    ) {}

    public function resolve(IconResolutionRequest $request): array
    {
        if ($request->iconSetName !== null) {
            $icons = $this->iconSetRegistry->get(
                $request->iconSetName,
                $request->sourceClass,
            );

            return array_map(fn (ShortcutIcon $i) => $this->enrich($i), $icons);
        }

        if ($request->iconsArray !== null) {
            /** @var list<ShortcutIcon> $result */
            $result = array_map(
                fn (ShortcutIcon $i) => $this->enrich($i),
                $request->iconsArray
            );

            return $result;
        }

        if ($request->iconObject !== null) {
            return [$this->enrich($request->iconObject)];
        }

        if ($request->iconString !== null) {
            return [$this->buildFromShorthand(
                $request->iconString,
                $request->sizesHint,
                $request->typeHint,
            )];
        }

        return [];
    }

    private function buildFromShorthand(
        string $src,
        ?string $sizes,
        ?string $type
    ): ShortcutIcon {
        if ($sizes === null || $type === null) {
            $metadata = $this->probe->probe($src);
            if ($metadata !== null) {
                $sizes ??= $metadata->sizes;
                $type ??= $metadata->type;
            }
        }

        return new ShortcutIcon(
            src: $src,
            sizes: $sizes,
            type: $type,
        );
    }

    private function enrich(ShortcutIcon $icon): ShortcutIcon
    {
        if ($icon->sizes !== null && $icon->type !== null) {
            return $icon;
        }

        $metadata = $this->probe->probe($icon->src);
        if ($metadata === null) {
            return $icon;
        }

        return new ShortcutIcon(
            src: $icon->src,
            sizes: $icon->sizes ?? $metadata->sizes,
            type: $icon->type ?? $metadata->type,
            purpose: $icon->purpose,
        );
    }
}
