<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Core\Shortcuts\DefaultIconResolver;
use SlFomin\PwaLaravel\Core\Shortcuts\IconMetadata;
use SlFomin\PwaLaravel\Core\Shortcuts\IconMetadataProbe;
use SlFomin\PwaLaravel\Core\Shortcuts\IconResolutionRequest;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

function makeProbe(?IconMetadata $metadata): IconMetadataProbe
{
    return new class ($metadata) implements IconMetadataProbe
    {
        public function __construct(private readonly ?IconMetadata $meta) {}

        public function probe(string $src): ?IconMetadata
        {
            return $this->meta;
        }
    };
}

// --- Empty input ---

it('returns empty array when no icon declared', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(null));

    expect($resolver->resolve(new IconResolutionRequest()))->toBe([]);
});

// --- String shorthand form ---

it('string form wraps src in a single ShortcutIcon', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(null));
    $result = $resolver->resolve(new IconResolutionRequest(iconString: '/icon.png'));

    expect($result)->toHaveCount(1)
        ->and($result[0])->toBeInstanceOf(ShortcutIcon::class)
        ->and($result[0]->src)->toBe('/icon.png');
});

it('string form auto-probes missing sizes and type', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(new IconMetadata('192x192', 'image/png')));
    $result = $resolver->resolve(new IconResolutionRequest(iconString: '/icon.png'));

    expect($result[0]->sizes)->toBe('192x192')
        ->and($result[0]->type)->toBe('image/png');
});

it('string form respects explicit sizesHint over probe', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(new IconMetadata('512x512', 'image/webp')));
    $result = $resolver->resolve(new IconResolutionRequest(
        iconString: '/icon.png',
        sizesHint: '96x96',
        typeHint: 'image/png',
    ));

    expect($result[0]->sizes)->toBe('96x96')
        ->and($result[0]->type)->toBe('image/png');
});

it('string form leaves sizes null when probe returns null', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(null));
    $result = $resolver->resolve(new IconResolutionRequest(iconString: '/icon.png'));

    expect($result[0]->sizes)->toBeNull()
        ->and($result[0]->type)->toBeNull();
});

// --- ShortcutIcon object form ---

it('ShortcutIcon form enriches missing sizes and type', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(new IconMetadata('192x192', 'image/png')));
    $result = $resolver->resolve(new IconResolutionRequest(iconObject: new ShortcutIcon('/icon.png')));

    expect($result[0]->sizes)->toBe('192x192')
        ->and($result[0]->type)->toBe('image/png');
});

it('ShortcutIcon form does not overwrite user-provided sizes and type', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(new IconMetadata('512x512', 'image/webp')));
    $icon = new ShortcutIcon('/icon.png', '192x192', 'image/png');
    $result = $resolver->resolve(new IconResolutionRequest(iconObject: $icon));

    expect($result[0]->sizes)->toBe('192x192')
        ->and($result[0]->type)->toBe('image/png');
});

it('ShortcutIcon form preserves purpose when enriching', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(new IconMetadata('192x192', 'image/png')));
    $icon = new ShortcutIcon('/icon.png', purpose: 'maskable');
    $result = $resolver->resolve(new IconResolutionRequest(iconObject: $icon));

    expect($result[0]->purpose)->toBe('maskable');
});

it('ShortcutIcon form skips probe when both sizes and type already set', function (): void {
    $probeCalled = false;
    $probe = new class ($probeCalled) implements IconMetadataProbe
    {
        public function __construct(private bool &$called) {}

        public function probe(string $src): ?IconMetadata
        {
            $this->called = true;

            return null;
        }
    };

    $resolver = new DefaultIconResolver($probe);
    $icon = new ShortcutIcon('/icon.png', '192x192', 'image/png');
    $resolver->resolve(new IconResolutionRequest(iconObject: $icon));

    expect($probeCalled)->toBeFalse();
});

// --- icons array form ---

it('icons array form enriches each entry', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(new IconMetadata('96x96', 'image/png')));
    $icons = [new ShortcutIcon('/a.png'), new ShortcutIcon('/b.png', '192x192')];
    $result = $resolver->resolve(new IconResolutionRequest(iconsArray: $icons));

    expect($result)->toHaveCount(2)
        ->and($result[0]->sizes)->toBe('96x96')    // probed
        ->and($result[1]->sizes)->toBe('192x192'); // user-provided wins
});

it('icons array form returns empty array for empty input', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(null));
    $result = $resolver->resolve(new IconResolutionRequest(iconsArray: []));

    expect($result)->toBe([]);
});
