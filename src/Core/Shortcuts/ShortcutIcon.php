<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Core\Shortcuts;

/**
 * Represents a single icon resource entry within a PWA shortcut.
 *
 * Structurally identical to ImageResource in the W3C Web App Manifest spec.
 * The spec reuses ImageResource for both top-level `icons[]` and per-shortcut
 * `icons[]`; this DTO is intentionally narrow to the shortcut use case. Fields
 * defined here are the subset relevant to OS shortcut rendering — `label` and
 * `platform` from ImageResource are not currently exposed (may be added in v2).
 *
 * @see https://www.w3.org/TR/image-resource/  W3C: ImageResource (full spec)
 * @see https://www.w3.org/TR/manifest/#icons-member  W3C: top-level icons (same structure)
 * @see https://developer.mozilla.org/en-US/docs/Web/Manifest/icons  MDN: icons
 */
final readonly class ShortcutIcon
{
    /**
     * @param  string  $src
     *                       URL of the icon resource. Same-origin relative paths are recommended
     *                       (e.g. '/icons/login.png'). Absolute URLs to CDNs are valid but cannot
     *                       be auto-probed for sizes/type at build time.
     * @param  string|null  $sizes
     *                              Space-separated list of icon dimensions in `WIDTHxHEIGHT` format,
     *                              or the keyword `any` for scalable (SVG) icons. Multiple sizes in
     *                              one entry indicate the same file is suitable at multiple dimensions
     *                              (rare — typically each ImageResource lists one size).
     *                              Examples: "192x192", "96x96 192x192", "any".
     *                              When omitted, the browser fetches the resource and determines its
     *                              dimensions itself — valid per spec but adds a round-trip.
     * @param  string|null  $type
     *                             MIME type of the resource (e.g. "image/png", "image/svg+xml").
     *                             Optional but recommended; lets the browser skip resources whose
     *                             format it cannot decode without fetching them first.
     * @param  string|null  $purpose
     *                                Space-separated list of purpose tokens defining the icon's intended
     *                                use. Allowed tokens (validated at construction):
     *                                - "any"        — default; usable for any purpose.
     *                                - "maskable"   — designed for adaptive icons with a safe zone;
     *                                Android launchers apply a system mask.
     *                                - "monochrome" — single-color icon for notification badges and
     *                                OS theming surfaces.
     *                                Tokens can be combined: "any maskable". `null` is equivalent to "any".
     *
     * @throws \InvalidArgumentException If `$purpose` contains unknown tokens.
     *
     * @see https://www.w3.org/TR/manifest/#purpose-member
     * @see https://web.dev/maskable-icon/  Maskable icons explained
     * @see https://developer.mozilla.org/en-US/docs/Web/Manifest/icons#purpose
     */
    public function __construct(
        public string $src,
        public ?string $sizes = null,
        public ?string $type = null,
        public ?string $purpose = null,
    ) {
        if ($purpose !== null) {
            $this->validatePurpose($purpose);
        }
    }

    /**
     * Serializes to the W3C ImageResource JSON structure, omitting null fields.
     *
     * @return array{src: string, sizes?: string, type?: string, purpose?: string}
     */
    public function toArray(): array
    {
        return array_filter(
            [
                'src' => $this->src,
                'sizes' => $this->sizes,
                'type' => $this->type,
                'purpose' => $this->purpose,
            ],
            fn ($value) => $value !== null,
        );
    }

    private function validatePurpose(string $purpose): void
    {
        $allowed = array_map(fn (ShortcutIconPurpose $p) => $p->value, ShortcutIconPurpose::cases());
        foreach (explode(' ', $purpose) as $token) {
            if ($token === '') {
                continue;
            }
            if (! in_array($token, $allowed, true)) {
                throw new \InvalidArgumentException(sprintf(
                    "ShortcutIcon: invalid purpose token '%s'. Allowed: %s.",
                    $token,
                    implode(', ', $allowed),
                ));
            }
        }
    }
}
