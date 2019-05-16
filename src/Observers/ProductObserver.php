<?php

namespace Viviniko\Catalog\Observers;

use Illuminate\Support\Facades\Auth;
use Viviniko\Catalog\Models\Product;

class ProductObserver
{
    public function saving(Product $product)
    {
        if (!$product->exists() || empty($product->created_by)) {
            $product->created_by = Auth::check() ? Auth::user()->name : '';
        }

        if (empty($product->updated_by)) {
            $product->updated_by = Auth::check() ? Auth::user()->name : '';
        }
    }

    public function saved(Product $product)
    {
        if (!$product->is_active) {
            $product->unsearchable();
        }
    }

    public function deleting(Product $product)
    {
        $product->items()->delete();
        $product->pictures()->sync([]);
        $product->specs()->sync([]);
        $product->specGroups()->sync([]);
        $product->attrs()->sync([]);
        $product->manufacturerProduct()->delete();
        $product->unsearchable();
    }
}