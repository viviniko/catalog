<?php

namespace Viviniko\Catalog\Repositories\Product;

use Viviniko\Repository\EloquentRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EloquentProduct extends EloquentRepository implements ProductRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('catalog.product'));
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestProducts($take, $columns = ['*'])
    {
        return $this->createQuery()->latest()->limit($take)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function attachProductSpecGroup($productId, $specificationGroupId, array $specifications = [])
    {
        if ($product = $this->find($productId)) {
            $product->specGroups()->attach($specificationGroupId, $specifications);
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function updateProductSpecGroup($productId, $specificationGroupId, array $specifications = [])
    {
        if ($product = $this->find($productId)) {
            $product->specGroups()->updateExistingPivot($specificationGroupId, $specifications);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detachProductSpecGroup($productId, $groupId)
    {
        if ($product = $this->find($productId)) {
            $product->specGroups()->detach($groupId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getProductSpec($productId)
    {
        return DB::table(Config::get('catalog.product_spec_table'))
            ->where('product_id', $productId)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function resetProductSelectedSpec($productId, $specificationId)
    {
        DB::table(Config::get('catalog.product_spec_table'))
            ->where('product_id', $productId)
            ->where('is_selected', 1)
            ->whereIn('spec_id', function ($query) use ($specificationId) {
                $query->select('id')->from(Config::get('catalog.specs_table'))->where('group_id', function ($subQuery) use ($specificationId) {
                    $subQuery->select('group_id')->from(Config::get('catalog.specs_table'))->where('id', $specificationId);
                });
            })->update(['is_selected' => 0]);
    }
}