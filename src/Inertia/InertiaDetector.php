<?php

declare(strict_types=1);

namespace SlFomin\PwaLaravel\Inertia;

use Illuminate\Http\Request;
use Inertia\Inertia;

final class InertiaDetector
{
    public static function installed(): bool
    {
        return class_exists(Inertia::class);
    }

    public static function isInertiaRequest(?Request $request = null): bool
    {
        $request ??= request();

        return $request->header('X-Inertia') !== null;
    }

    public static function isSsr(?Request $request = null): bool
    {
        $request ??= request();

        return $request->header('X-Inertia-SSR') !== null;
    }
}
