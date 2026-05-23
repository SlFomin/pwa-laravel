<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Core\Shortcuts\DefaultIconResolver;
use SlFomin\PwaLaravel\Core\Shortcuts\IconMetadata;
use SlFomin\PwaLaravel\Core\Shortcuts\IconMetadataProbe;
use SlFomin\PwaLaravel\Core\Shortcuts\IconResolutionRequest;
use SlFomin\PwaLaravel\Core\Shortcuts\IconSetRegistry;
use SlFomin\PwaLaravel\Core\Shortcuts\ShortcutIcon;

function makeProbe(?IconMetadata $metadata): IconMetadataProbe
{
    return new class($metadata) implements IconMetadataProbe
    {
        public function __construct(private readonly ?IconMetadata $meta) {}

        public function probe(string $src): ?IconMetadata
        {
            return $this->meta;
        }
    };
}

function makeNullIconSetRegistry(): IconSetRegistry
{
    return new class implements IconSetRegistry
    {
        public function get(string $name, ?string $contextClass = null): array
        {
            throw new RuntimeException('Registry not expected in this test');
        }

        public function has(string $name, ?string $contextClass = null): bool
        {
            return false;
        }

        public function all(): array
        {
            return [];
        }
    };
}

function makeFilledIconSetRegistry(array $sets): IconSetRegistry
{
    return new class($sets) implements IconSetRegistry
    {
        public function __construct(private readonly array $sets) {}

        public function get(string $name, ?string $contextClass = null): array
        {
            return $this->sets[$name] ?? [];
        }

        public function has(string $name, ?string $contextClass = null): bool
        {
            return isset($this->sets[$name]);
        }

        public function all(): array
        {
            return $this->sets;
        }
    };
}

// --- Empty input ---

it('returns empty array when no icon declared', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(null), makeNullIconSetRegistry());

    expect($resolver->resolve(new IconResolutionRequest))->toBe([]);
});

// --- String shorthand form ---

it('string form wraps src in a single ShortcutIcon', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(null), makeNullIconSetRegistry());
    $result = $resolver->resolve(new IconResolutionRequest(iconString: '/icon.png'));

    expect($result)->toHaveCount(1)
        ->and($result[0])->toBeInstanceOf(ShortcutIcon::class)
        ->and($result[0]->src)->toBe('/icon.png');
});

it('string form auto-probes missing sizes and type', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(new IconMetadata('192x192', 'image/png')), makeNullIconSetRegistry());
    $result = $resolver->resolve(new IconResolutionRequest(iconString: '/icon.png'));

    expect($result[0]->sizes)->toBe('192x192')
        ->and($result[0]->type)->toBe('image/png');
});

it('string form respects explicit sizesHint over probe', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(new IconMetadata('512x512', 'image/webp')), makeNullIconSetRegistry());
    $result = $resolver->resolve(new IconResolutionRequest(
        iconString: '/icon.png',
        sizesHint: '96x96',
        typeHint: 'image/png',
    ));

    expect($result[0]->sizes)->toBe('96x96')
        ->and($result[0]->type)->toBe('image/png');
});

it('string form leaves sizes null when probe returns null', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(null), makeNullIconSetRegistry());
    $result = $resolver->resolve(new IconResolutionRequest(iconString: '/icon.png'));

    expect($result[0]->sizes)->toBeNull()
        ->and($result[0]->type)->toBeNull();
});

// --- ShortcutIcon object form ---

it('ShortcutIcon form enriches missing sizes and type', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(new IconMetadata('192x192', 'image/png')), makeNullIconSetRegistry());
    $result = $resolver->resolve(new IconResolutionRequest(iconObject: new ShortcutIcon('/icon.png')));

    expect($result[0]->sizes)->toBe('192x192')
        ->and($result[0]->type)->toBe('image/png');
});

it('ShortcutIcon form does not overwrite user-provided sizes and type', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(new IconMetadata('512x512', 'image/webp')), makeNullIconSetRegistry());
    $icon = new ShortcutIcon('/icon.png', '192x192', 'image/png');
    $result = $resolver->resolve(new IconResolutionRequest(iconObject: $icon));

    expect($result[0]->sizes)->toBe('192x192')
        ->and($result[0]->type)->toBe('image/png');
});

it('ShortcutIcon form preserves purpose when enriching', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(new IconMetadata('192x192', 'image/png')), makeNullIconSetRegistry());
    $icon = new ShortcutIcon('/icon.png', purpose: 'maskable');
    $result = $resolver->resolve(new IconResolutionRequest(iconObject: $icon));

    expect($result[0]->purpose)->toBe('maskable');
});

it('ShortcutIcon form skips probe when both sizes and type already set', function (): void {
    $probeCalled = false;
    $probe = new class($probeCalled) implements IconMetadataProbe
    {
        public function __construct(private bool &$called) {}

        public function probe(string $src): ?IconMetadata
        {
            $this->called = true;

            return null;
        }
    };

    $resolver = new DefaultIconResolver($probe, makeNullIconSetRegistry());
    $icon = new ShortcutIcon('/icon.png', '192x192', 'image/png');
    $resolver->resolve(new IconResolutionRequest(iconObject: $icon));

    expect($probeCalled)->toBeFalse();
});

// --- icons array form ---

it('icons array form enriches each entry', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(new IconMetadata('96x96', 'image/png')), makeNullIconSetRegistry());
    $icons = [new ShortcutIcon('/a.png'), new ShortcutIcon('/b.png', '192x192')];
    $result = $resolver->resolve(new IconResolutionRequest(iconsArray: $icons));

    expect($result)->toHaveCount(2)
        ->and($result[0]->sizes)->toBe('96x96')    // probed
        ->and($result[1]->sizes)->toBe('192x192'); // user-provided wins
});

it('icons array form returns empty array for empty input', function (): void {
    $resolver = new DefaultIconResolver(makeProbe(null), makeNullIconSetRegistry());
    $result = $resolver->resolve(new IconResolutionRequest(iconsArray: []));

    expect($result)->toBe([]);
});

// --- icon set form ---

it('iconSetName form returns icons from registry', function (): void {
    $icons = [new ShortcutIcon('/icons/auth-96.png', '96x96', 'image/png')];
    $resolver = new DefaultIconResolver(makeProbe(null), makeFilledIconSetRegistry(['auth' => $icons]));

    $result = $resolver->resolve(new IconResolutionRequest(iconSetName: 'auth'));

    expect($result)->toHaveCount(1)
        ->and($result[0]->src)->toBe('/icons/auth-96.png');
});

it('iconSetName form enriches icons returned by registry', function (): void {
    $icons = [new ShortcutIcon('/icons/auth.png')]; // no sizes/type
    $resolver = new DefaultIconResolver(
        makeProbe(new IconMetadata('96x96', 'image/png')),
        makeFilledIconSetRegistry(['auth' => $icons]),
    );

    $result = $resolver->resolve(new IconResolutionRequest(iconSetName: 'auth'));

    expect($result[0]->sizes)->toBe('96x96')
        ->and($result[0]->type)->toBe('image/png');
});

it('iconSetName form passes sourceClass to registry', function (): void {
    $passedClass = null;
    $registry = new class($passedClass) implements IconSetRegistry
    {
        public function __construct(private ?string &$passedClass) {}

        public function get(string $name, ?string $contextClass = null): array
        {
            $this->passedClass = $contextClass;

            return [];
        }

        public function has(string $name, ?string $contextClass = null): bool
        {
            return true;
        }

        public function all(): array
        {
            return [];
        }
    };

    $resolver = new DefaultIconResolver(makeProbe(null), $registry);
    $resolver->resolve(new IconResolutionRequest(
        iconSetName: 'auth',
        sourceClass: 'App\\Http\\Controllers\\AuthController',
    ));

    expect($passedClass)->toBe('App\\Http\\Controllers\\AuthController');
});
