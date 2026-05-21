<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Contracts;

use SlFomin\PwaLaravel\Exceptions\IconGenerationException;

interface IconGenerator
{
    /**
     * @param  string  $sourcePath  Absolute path to source PNG/JPG (≥512×512, square)
     * @param  string  $outputPath  Absolute output directory
     * @return list<array{src: string, sizes: string, type: string, purpose?: string}>
     *
     * @throws IconGenerationException
     */
    public function generate(string $sourcePath, string $outputPath): array;

    /**
     * @throws IconGenerationException
     */
    public function validateSource(string $sourcePath): void;
}
