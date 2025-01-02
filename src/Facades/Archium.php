<?php

namespace PanicDev\Archium\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \PanicDev\Archium\Archium
 */
class Archium extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \PanicDev\Archium\Archium::class;
    }
}
