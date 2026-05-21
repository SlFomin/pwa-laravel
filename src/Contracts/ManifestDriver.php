<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Contracts;

use Illuminate\Http\Request;
use SlFomin\PwaLaravel\Manifest\ManifestBuilder;

interface ManifestDriver
{
    public function resolve(Request $request): ManifestBuilder;

    public function url(Request $request): string;

    /**
     * @return array<string, string>
     */
    public function linkAttributes(Request $request): array;
}
