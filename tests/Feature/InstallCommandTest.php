<?php

declare(strict_types=1);

use SlFomin\PwaLaravel\Console\PublishManifestCommand;

// --- pwa:publish-manifest ---

it('publishes manifest file to configured static_path', function (): void {
    $path = sys_get_temp_dir().'/pwa_test_'.uniqid().'/manifest.webmanifest';

    config([
        'pwa.manifest.static_path' => $path,
        'pwa.manifest.data' => [
            'name' => 'Test App',
            'short_name' => 'Test',
            'start_url' => '/',
            'display' => 'standalone',
        ],
    ]);

    $this->artisan('pwa:publish-manifest')
        ->assertSuccessful();

    expect(file_exists($path))->toBeTrue();

    $json = json_decode(file_get_contents($path), true);
    expect($json['name'])->toBe('Test App');
    expect($json['display'])->toBe('standalone');

    @unlink($path);
    @rmdir(dirname($path));
});

it('publishes manifest to custom --path', function (): void {
    $path = sys_get_temp_dir().'/pwa_custom_'.uniqid().'/manifest.webmanifest';

    config([
        'pwa.manifest.data' => [
            'name' => 'My App',
            'short_name' => 'App',
            'start_url' => '/',
            'display' => 'standalone',
        ],
    ]);

    $this->artisan('pwa:publish-manifest', ['--path' => $path])
        ->assertSuccessful();

    expect(file_exists($path))->toBeTrue();

    @unlink($path);
    @rmdir(dirname($path));
});

it('publishes manifest with --pretty flag as indented JSON', function (): void {
    $path = sys_get_temp_dir().'/pwa_pretty_'.uniqid().'/manifest.webmanifest';

    config([
        'pwa.manifest.static_path' => $path,
        'pwa.manifest.data' => [
            'name' => 'Pretty App',
            'short_name' => 'Pretty',
            'start_url' => '/',
            'display' => 'standalone',
        ],
    ]);

    $this->artisan('pwa:publish-manifest', ['--pretty' => true])
        ->assertSuccessful();

    $content = file_get_contents($path);
    expect($content)->toContain("\n    ");

    @unlink($path);
    @rmdir(dirname($path));
});

it('fails with an error when manifest data is missing required fields', function (): void {
    config([
        'pwa.manifest.static_path' => sys_get_temp_dir().'/manifest.webmanifest',
        'pwa.manifest.data' => [
            'name' => 'No Short Name',
            // missing short_name, start_url, display
        ],
    ]);

    $this->artisan('pwa:publish-manifest')
        ->assertFailed();
});

it('creates output directory if it does not exist', function (): void {
    $dir = sys_get_temp_dir().'/pwa_newdir_'.uniqid();
    $path = $dir.'/sub/manifest.webmanifest';

    config([
        'pwa.manifest.static_path' => $path,
        'pwa.manifest.data' => [
            'name' => 'Dir Test',
            'short_name' => 'Dir',
            'start_url' => '/',
            'display' => 'standalone',
        ],
    ]);

    $this->artisan('pwa:publish-manifest')
        ->assertSuccessful();

    expect(file_exists($path))->toBeTrue();

    @unlink($path);
    @rmdir(dirname($path));
    @rmdir($dir);
});

it('fails when path is not configured', function (): void {
    config([
        'pwa.manifest.static_path' => null,
        'pwa.manifest.data' => [
            'name' => 'Test',
            'short_name' => 'Test',
            'start_url' => '/',
            'display' => 'standalone',
        ],
    ]);

    $this->artisan('pwa:publish-manifest')
        ->assertFailed();
});

it('prompts before overwriting existing manifest without --force', function (): void {
    // Fixed path so we can build the exact confirmation question string
    $path = sys_get_temp_dir().'/pwa_overwrite_exact_test.webmanifest';
    file_put_contents($path, '{"existing":true}');

    config([
        'pwa.manifest.static_path' => $path,
        'pwa.manifest.data' => [
            'name' => 'Test', 'short_name' => 'T', 'start_url' => '/', 'display' => 'standalone',
        ],
    ]);

    $this->artisan('pwa:publish-manifest')
        ->expectsConfirmation("File already exists at {$path}. Overwrite?", 'no')
        ->assertFailed();

    expect(file_get_contents($path))->toBe('{"existing":true}');

    @unlink($path);
});

it('overwrites existing manifest silently with --force', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'pwa_force_');
    file_put_contents($path, '{"old":true}');

    config([
        'pwa.manifest.static_path' => $path,
        'pwa.manifest.data' => [
            'name' => 'New App', 'short_name' => 'New', 'start_url' => '/', 'display' => 'standalone',
        ],
    ]);

    $this->artisan('pwa:publish-manifest', ['--force' => true])
        ->assertSuccessful();

    $json = json_decode(file_get_contents($path), true);
    expect($json['name'])->toBe('New App');

    @unlink($path);
});

it('PublishManifestCommand is registered and resolvable', function (): void {
    expect(PublishManifestCommand::class)->toBeString();
    $cmd = app(PublishManifestCommand::class);
    expect($cmd)->toBeInstanceOf(PublishManifestCommand::class);
});
