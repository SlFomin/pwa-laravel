<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Contracts;

interface ServiceWorkerStrategy
{
    public function path(): string;

    public function url(): string;

    public function exists(): bool;

    /**
     * @return array<string, mixed>
     */
    public function viteOptions(): array;
}
