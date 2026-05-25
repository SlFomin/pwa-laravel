<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Core\Shortcuts\FilesystemIconMetadataProbe;

beforeEach(function (): void {
    $this->publicDir = sys_get_temp_dir().'/pwa_probe_'.uniqid();
    mkdir($this->publicDir.'/icons', 0755, true);
    $this->probe = new FilesystemIconMetadataProbe($this->publicDir);
});

afterEach(function (): void {
    $dir = $this->publicDir;
    if (is_dir($dir)) {
        foreach (glob($dir.'/icons/*') ?: [] as $f) {
            unlink($f);
        }
        rmdir($dir.'/icons');
        rmdir($dir);
    }
});

// --- Remote URLs ---

it('returns null for http:// URLs without touching filesystem', function (): void {
    expect($this->probe->probe('http://example.com/icon.png'))->toBeNull();
});

it('returns null for https:// URLs without touching filesystem', function (): void {
    expect($this->probe->probe('https://cdn.example.com/icon.svg'))->toBeNull();
});

// --- Missing / unreadable files ---

it('returns null for a non-existent file', function (): void {
    expect($this->probe->probe('/icons/nonexistent.png'))->toBeNull();
});

// --- SVG ---

it('returns sizes:any and type:image/svg+xml for SVG', function (): void {
    file_put_contents(
        $this->publicDir.'/icons/icon.svg',
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"/>'
    );

    $meta = $this->probe->probe('/icons/icon.svg');

    expect($meta)->not->toBeNull()
        ->and($meta->sizes)->toBe('any')
        ->and($meta->type)->toBe('image/svg+xml');
});

// --- PNG ---

it('returns WxH and image/png for a valid PNG', function (): void {
    $fixture = __DIR__.'/../fixtures/icon-source-512.png';
    copy($fixture, $this->publicDir.'/icons/icon.png');

    $meta = $this->probe->probe('/icons/icon.png');

    expect($meta)->not->toBeNull()
        ->and($meta->sizes)->toBe('512x512')
        ->and($meta->type)->toBe('image/png');
});

// --- Non-image file ---

it('returns null for a plain text file', function (): void {
    file_put_contents($this->publicDir.'/icons/data.txt', 'not an image');

    expect($this->probe->probe('/icons/data.txt'))->toBeNull();
});

// --- Path normalisation ---

it('handles leading slash in src correctly', function (): void {
    file_put_contents(
        $this->publicDir.'/icons/icon.svg',
        '<svg xmlns="http://www.w3.org/2000/svg"/>'
    );

    expect($this->probe->probe('/icons/icon.svg'))->not->toBeNull();
});

it('handles src without leading slash', function (): void {
    file_put_contents(
        $this->publicDir.'/icons/icon.svg',
        '<svg xmlns="http://www.w3.org/2000/svg"/>'
    );

    expect($this->probe->probe('icons/icon.svg'))->not->toBeNull();
});
