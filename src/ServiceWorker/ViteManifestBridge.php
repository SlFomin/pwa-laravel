<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\ServiceWorker;

final class ViteManifestBridge
{
    /** @var array<string, array<string, mixed>>|null */
    protected ?array $manifest = null;

    /**
     * @return array<string, array<string, mixed>>
     */
    public function load(): array
    {
        if ($this->manifest !== null) {
            return $this->manifest;
        }

        $path = config('pwa.vite.manifest_path');
        if (! is_string($path) || ! file_exists($path)) {
            return $this->manifest = [];
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            return $this->manifest = [];
        }

        $decoded = json_decode($contents, true);

        return $this->manifest = is_array($decoded) ? $decoded : [];
    }

    public function asset(string $entry): ?string
    {
        $manifest = $this->load();
        if (! isset($manifest[$entry]['file'])) {
            return null;
        }

        $base = rtrim(config('pwa.vite.base_url', '/build/'), '/');

        return "{$base}/{$manifest[$entry]['file']}";
    }

    public function exists(): bool
    {
        $path = config('pwa.vite.manifest_path');

        return is_string($path) && file_exists($path);
    }

    public function clear(): void
    {
        $this->manifest = null;
    }
}
