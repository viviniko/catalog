<?php

namespace Viviniko\Catalog\Enums;

class AttrType
{
    const OPTION = 0;

    public static function values()
    {
        return [
            static::OPTION => 'OPTION',
        ];
    }
}