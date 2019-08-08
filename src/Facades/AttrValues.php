<?php

namespace Viviniko\Catalog\Facades;

use Illuminate\Support\Facades\Facade;

class AttrValues extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Viviniko\Catalog\Repositories\AttrValue\AttrValueRepository::class;
    }
}