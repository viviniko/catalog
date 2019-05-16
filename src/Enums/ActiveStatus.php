<?php

namespace Viviniko\Catalog\Enums;

class ActiveStatus
{
    const ACTIVE = 1;
    const DISABLED = 0;

    public static function values()
    {
        return [
            static::ACTIVE => 'Active',
            static::DISABLED => 'Disabled',
        ];
    }
}