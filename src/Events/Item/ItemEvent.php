<?php

namespace Viviniko\Catalog\Events\Item;

use Viviniko\Catalog\Models\Item;

class ItemEvent
{
    public $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }
}