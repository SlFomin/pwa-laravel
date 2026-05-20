<?php

namespace SlFomin\PwaLaravel\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \SlFomin\PwaLaravel\PwaLaravel
 */
class PwaLaravel extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \SlFomin\PwaLaravel\PwaLaravel::class;
    }
}
