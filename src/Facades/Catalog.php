<?php

namespace Viviniko\Catalog\Facades;

use Illuminate\Support\Facades\Facade;

class Catalog extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'catalog';
    }
}