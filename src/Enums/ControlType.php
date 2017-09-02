<?php

namespace Viviniko\Catalog\Enums;

class ControlType
{
    const DROPDOWN = '0';
    const IMGSQUARES = '1';

    public static function values()
    {
        return [
            static::DROPDOWN => 'Drop-down List',
            static::IMGSQUARES => 'Image Squares',
        ];
    }
}