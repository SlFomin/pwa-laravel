<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Exceptions\IconGenerationException;
use SlFomin\PwaLaravel\Manifest\IconProcessor;

// Paths to test fixtures
const ICON_512 = __DIR__.'/../fixtures/icon-source-512.png';
const ICON_TOO_SMALL = __DIR__.'/../fixtures/icon-source-too-small.png';
const ICON_NONSQUARE = __DIR__.'/../fixtures/icon-source-nonsquare.png';
const ICON_UNSUPPORTED = __DIR__.'/../fixtures/icon-source-unsupported.gif';

// Shared temp output dir, cleaned after each test
beforeEach(function (): void {
    $this->outputDir = sys_get_temp_dir().'/pwa_icons_'.uniqid();

    config([
        'pwa.icons.sizes' => [72, 192],
        'pwa.icons.maskable_sizes' => [192],
        'pwa.icons.maskable_padding' => 0.1,
        'pwa.icons.maskable_background' => '#ffffff',
        'pwa.icons.apple_touch_size' => 180,
        'pwa.icons.favicon_sizes' => [16, 32],
        'pwa.icons.output_url_prefix' => '/icons',
        'pwa.icons.quality' => 90,
        'pwa.icons.generate_maskable' => true,
        'pwa.icons.generate_apple_touch' => true,
        'pwa.icons.generate_favicon' => true,
    ]);
});

afterEach(function (): void {
    if (is_dir($this->outputDir)) {
        foreach (new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->outputDir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        ) as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->outputDir);
    }
});

// --- validateSource ---

it('validateSource passes for valid 512x512 PNG', function (): void {
    $processor = new IconProcessor;
    expect(fn () => $processor->validateSource(ICON_512))->not->toThrow(IconGenerationException::class);
});

it('validateSource throws when file does not exist', function (): void {
    $processor = new IconProcessor;
    $processor->validateSource('/nonexistent/icon.png');
})->throws(IconGenerationException::class, 'Source icon not found');

it('validateSource throws when image is too small', function (): void {
    $processor = new IconProcessor;
    $processor->validateSource(ICON_TOO_SMALL);
})->throws(IconGenerationException::class, '512×512');

it('validateSource throws when image is not square', function (): void {
    $processor = new IconProcessor;
    $processor->validateSource(ICON_NONSQUARE);
})->throws(IconGenerationException::class, 'square');

it('validateSource throws for unsupported image format', function (): void {
    $processor = new IconProcessor;
    $processor->validateSource(ICON_UNSUPPORTED);
})->throws(IconGenerationException::class, 'Unsupported image format');

// --- generate: output files ---

it('generate creates output directory when it does not exist', function (): void {
    $processor = new IconProcessor;
    $processor->generate(ICON_512, $this->outputDir);

    expect(is_dir($this->outputDir))->toBeTrue();
});

it('generate creates standard icons for each configured size', function (): void {
    $processor = new IconProcessor;
    $processor->generate(ICON_512, $this->outputDir);

    expect(file_exists("{$this->outputDir}/icon-72x72.png"))->toBeTrue()
        ->and(file_exists("{$this->outputDir}/icon-192x192.png"))->toBeTrue();
});

it('generate creates maskable icons', function (): void {
    $processor = new IconProcessor;
    $processor->generate(ICON_512, $this->outputDir);

    expect(file_exists("{$this->outputDir}/icon-maskable-192x192.png"))->toBeTrue();
});

it('generate creates apple-touch-icon', function (): void {
    $processor = new IconProcessor;
    $processor->generate(ICON_512, $this->outputDir);

    expect(file_exists("{$this->outputDir}/apple-touch-icon.png"))->toBeTrue();
});

it('generate creates favicons for each configured size', function (): void {
    $processor = new IconProcessor;
    $processor->generate(ICON_512, $this->outputDir);

    expect(file_exists("{$this->outputDir}/favicon-16x16.png"))->toBeTrue()
        ->and(file_exists("{$this->outputDir}/favicon-32x32.png"))->toBeTrue();
});

it('generate returns icon list with correct structure', function (): void {
    $processor = new IconProcessor;
    $icons = $processor->generate(ICON_512, $this->outputDir);

    expect($icons)->toBeArray()->not->toBeEmpty();

    foreach ($icons as $icon) {
        expect($icon)->toHaveKeys(['src', 'sizes', 'type', 'purpose'])
            ->and($icon['type'])->toBe('image/png');
    }
});

it('generate returns any-purpose icons for standard sizes', function (): void {
    $processor = new IconProcessor;
    $icons = $processor->generate(ICON_512, $this->outputDir);

    $anyIcons = array_filter($icons, fn (array $i): bool => $i['purpose'] === 'any');
    expect($anyIcons)->toHaveCount(2); // sizes [72, 192]
});

it('generate returns maskable-purpose icons for maskable sizes', function (): void {
    $processor = new IconProcessor;
    $icons = $processor->generate(ICON_512, $this->outputDir);

    $maskable = array_filter($icons, fn (array $i): bool => $i['purpose'] === 'maskable');
    expect($maskable)->toHaveCount(1); // maskable_sizes [192]
});

it('generate skips maskable when disabled in config', function (): void {
    config(['pwa.icons.generate_maskable' => false]);

    $processor = new IconProcessor;
    $icons = $processor->generate(ICON_512, $this->outputDir);

    $maskable = array_filter($icons, fn (array $i): bool => $i['purpose'] === 'maskable');
    expect($maskable)->toBeEmpty()
        ->and(file_exists("{$this->outputDir}/icon-maskable-192x192.png"))->toBeFalse();
});

it('generate skips apple-touch when disabled in config', function (): void {
    config(['pwa.icons.generate_apple_touch' => false]);

    $processor = new IconProcessor;
    $processor->generate(ICON_512, $this->outputDir);

    expect(file_exists("{$this->outputDir}/apple-touch-icon.png"))->toBeFalse();
});

it('generate skips favicon when disabled in config', function (): void {
    config(['pwa.icons.generate_favicon' => false]);

    $processor = new IconProcessor;
    $processor->generate(ICON_512, $this->outputDir);

    expect(file_exists("{$this->outputDir}/favicon-16x16.png"))->toBeFalse();
});

it('generated icon files are valid PNG images', function (): void {
    $processor = new IconProcessor;
    $processor->generate(ICON_512, $this->outputDir);

    $info = getimagesize("{$this->outputDir}/icon-192x192.png");
    expect($info)->not->toBeFalse()
        ->and($info[0])->toBe(192)
        ->and($info[1])->toBe(192)
        ->and($info[2])->toBe(IMAGETYPE_PNG);
});

it('generated maskable icon has correct dimensions', function (): void {
    $processor = new IconProcessor;
    $processor->generate(ICON_512, $this->outputDir);

    $info = getimagesize("{$this->outputDir}/icon-maskable-192x192.png");
    expect($info)->not->toBeFalse()
        ->and($info[0])->toBe(192)
        ->and($info[1])->toBe(192);
});

it('generate throws when source is invalid', function (): void {
    $processor = new IconProcessor;
    $processor->generate(ICON_TOO_SMALL, $this->outputDir);
})->throws(IconGenerationException::class);

it('icon src url uses configured output_url_prefix', function (): void {
    config(['pwa.icons.output_url_prefix' => '/pwa-icons']);

    $processor = new IconProcessor;
    $icons = $processor->generate(ICON_512, $this->outputDir);

    $first = reset($icons);
    expect($first['src'])->toStartWith('/pwa-icons/');
});
