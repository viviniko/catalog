<?php

namespace Viviniko\Catalog\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        DB::transaction(function () use ($product) {
            $product->unsearchable();
            $product->items()->delete();
            $product->manufacturerProduct()->delete();
            $product->specs()->delete();
            $product->tags()->delete();
            $product->favoritors()->delete();
        });
    }
}