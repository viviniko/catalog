<?php

namespace Viviniko\Catalog\Observers;

use Viviniko\Catalog\Models\ProductSpec;

class ProductSpecObserver
{
    public function deleting(ProductSpec $productSpec)
    {
        $productSpec->values()->delete();
    }
}