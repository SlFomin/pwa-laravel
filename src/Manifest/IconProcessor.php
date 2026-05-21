<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Manifest;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\ImageManager;
use SlFomin\PwaLaravel\Contracts\IconGenerator;
use SlFomin\PwaLaravel\Exceptions\IconGenerationException;

final class IconProcessor implements IconGenerator
{
    protected readonly ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new GdDriver);
    }

    public function generate(string $sourcePath, string $outputPath): array
    {
        $this->validateSource($sourcePath);

        if (! is_dir($outputPath) && ! mkdir($outputPath, 0755, true) && ! is_dir($outputPath)) {
            throw new IconGenerationException("Failed to create output directory: {$outputPath}");
        }

        if (! is_writable($outputPath)) {
            throw new IconGenerationException("Output directory is not writable: {$outputPath}");
        }

        $icons = $this->generateStandard($sourcePath, $outputPath);

        if ((bool) config('pwa.icons.generate_maskable', true)) {
            $icons = array_merge($icons, $this->generateMaskable($sourcePath, $outputPath));
        }

        if ((bool) config('pwa.icons.generate_apple_touch', true)) {
            $this->generateAppleTouch($sourcePath, $outputPath);
        }

        if ((bool) config('pwa.icons.generate_favicon', true)) {
            $this->generateFavicon($sourcePath, $outputPath);
        }

        return $icons;
    }

    public function validateSource(string $sourcePath): void
    {
        if (! file_exists($sourcePath)) {
            throw new IconGenerationException("Source icon not found: {$sourcePath}");
        }

        // Use a temporary error handler instead of @ to convert PHP warnings to a
        // meaningful exception without silencing unrelated errors in the call stack.
        set_error_handler(static fn (): bool => true);
        try {
            $info = getimagesize($sourcePath);
        } finally {
            restore_error_handler();
        }

        if ($info === false) {
            throw new IconGenerationException("Cannot read image metadata: {$sourcePath}");
        }

        [$width, $height] = $info;

        if ($width < 512 || $height < 512) {
            throw new IconGenerationException(
                "Source icon must be at least 512×512 px. Got {$width}×{$height}."
            );
        }

        if ($width !== $height) {
            throw new IconGenerationException(
                "Source icon must be square. Got {$width}×{$height}."
            );
        }

        if (! in_array($info[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP], true)) {
            throw new IconGenerationException(
                'Unsupported image format. Use PNG, JPEG, or WebP.'
            );
        }
    }

    /**
     * @return list<array{src: string, sizes: string, type: string, purpose: string}>
     */
    private function generateStandard(string $sourcePath, string $outputPath): array
    {
        /** @var list<int> $sizes */
        $sizes = config('pwa.icons.sizes', [72, 96, 128, 144, 152, 192, 384, 512]);
        $urlPrefix = rtrim((string) config('pwa.icons.output_url_prefix', '/icons'), '/');
        $quality = (int) config('pwa.icons.quality', 90);
        $icons = [];

        foreach ($sizes as $size) {
            $filename = "icon-{$size}x{$size}.png";
            $filepath = "{$outputPath}/{$filename}";

            $this->manager->read($sourcePath)->cover($size, $size)->save($filepath, $quality);

            $icons[] = [
                'src' => "{$urlPrefix}/{$filename}",
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'any',
            ];
        }

        return $icons;
    }

    /**
     * @return list<array{src: string, sizes: string, type: string, purpose: string}>
     */
    private function generateMaskable(string $sourcePath, string $outputPath): array
    {
        $padding = (float) config('pwa.icons.maskable_padding', 0.1);
        $bgColor = config('pwa.icons.maskable_background')
            ?? config('pwa.manifest.data.background_color', '#ffffff');
        /** @var list<int> $sizes */
        $sizes = config('pwa.icons.maskable_sizes', [192, 512]);
        $urlPrefix = rtrim((string) config('pwa.icons.output_url_prefix', '/icons'), '/');
        $quality = (int) config('pwa.icons.quality', 90);
        $icons = [];

        foreach ($sizes as $size) {
            $innerSize = (int) ($size * (1.0 - 2.0 * $padding));
            $offset = (int) (($size - $innerSize) / 2);
            $filename = "icon-maskable-{$size}x{$size}.png";
            $filepath = "{$outputPath}/{$filename}";

            $inner = $this->manager->read($sourcePath)->cover($innerSize, $innerSize);
            $this->manager->create($size, $size)->fill((string) $bgColor)->place($inner, 'top-left', $offset, $offset)->save($filepath, $quality);

            $icons[] = [
                'src' => "{$urlPrefix}/{$filename}",
                'sizes' => "{$size}x{$size}",
                'type' => 'image/png',
                'purpose' => 'maskable',
            ];
        }

        return $icons;
    }

    private function generateAppleTouch(string $sourcePath, string $outputPath): void
    {
        $size = (int) config('pwa.icons.apple_touch_size', 180);
        $quality = (int) config('pwa.icons.quality', 90);
        $this->manager->read($sourcePath)->cover($size, $size)->save("{$outputPath}/apple-touch-icon.png", $quality);
    }

    private function generateFavicon(string $sourcePath, string $outputPath): void
    {
        /** @var list<int> $sizes */
        $sizes = config('pwa.icons.favicon_sizes', [16, 32]);
        $quality = (int) config('pwa.icons.quality', 90);
        foreach ($sizes as $size) {
            $this->manager->read($sourcePath)->cover($size, $size)->save("{$outputPath}/favicon-{$size}x{$size}.png", $quality);
        }
    }
}
