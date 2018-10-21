<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Collection;

class ProductCover
{
    public $default;

    public $first;

    public $second;

    public function __construct($first, $second = null)
    {
        if (is_array($first)) {
            $first = collect($first);
        }
        if ($first instanceof Collection) {
            $this->first = $first->get(0);
            $this->second = $first->get(1);
        } else {
            $this->first = $first;
            $this->second = $second;
        }
        $this->default = $this->first;
    }

    public function __toString()
    {
        return (string) $this->default;
    }
}