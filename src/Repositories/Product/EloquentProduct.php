<?php

namespace Viviniko\Catalog\Repositories\Product;

use Illuminate\Support\Arr;
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
    public function paginate($perPage = null, $searchName = 'search', $search = null, $order = null)
    {
        $productTable = Config::get('catalog.products_table');
        $productManufacturerTable = Config::get('catalog.manufacturer_products_table');
        $productItemsTable = Config::get('catalog.items_table');
        $manufacturerTable = Config::get('catalog.manufacturers_table');
        $categoryTable = Config::get('catalog.categories_table');
        $taggablesTable = Config::get('tag.taggables_table');

        $this->searchRules = [
            'id' => "{$productTable}.id:=",
            'name' => "{$productTable}.name:like",
            'spu' => "{$productTable}.spu:like",
            'category' => "{$categoryTable}.id:=",
            'sku' => 'like',
            'manufacturer_id' => "{$manufacturerTable}.id:=",
            'manufacturer_product_sku' => "{$productManufacturerTable}.sku:like",
            'manufacturer_status' => "{$productManufacturerTable}.status:=",
            'price' => "{$productItemsTable}.price:=",
            'market_price' => "{$productItemsTable}.market_price:=",
            'quantity' => "{$productItemsTable}.quantity:=",
            'created_at' => "{$productTable}.created_at:betweenDate",
            'updated_at' => "{$productTable}.updated_at:betweenDate",
            'created_by' => 'like',
            'updated_by' => 'like',
            'is_active' => "{$productTable}.is_active:=",
        ];

        $query = $searchName ? (array)request()->get($searchName) : [];
        $search = array_merge($query, $search instanceof Arrayable ? $search->toArray() : (array)$search);
        $builder = $this->search($search)->select(["{$productTable}.*"])
            ->join($categoryTable, "{$productTable}.category_id", '=', "{$categoryTable}.id", 'left')
            ->join($productManufacturerTable, "{$productTable}.id", '=', "{$productManufacturerTable}.product_id", 'left')
            ->join($manufacturerTable, "{$manufacturerTable}.id", '=', "{$productManufacturerTable}.manufacturer_id", 'left')
            ->leftJoin($productItemsTable, function($join) use ($productTable, $productItemsTable) {
                $join->on("{$productTable}.id", '=', "{$productItemsTable}.product_id")
                    ->where("{$productItemsTable}.is_master", '=', '1');
            });
        if (!empty($order)) {
            $orders = [];
            if (is_string($order)) {
                $orders = [[$order, 'desc']];
            } else if (Arr::isAssoc($order)) {
                foreach ($order as $name => $direct) {
                    $orders[] = [$name, $direct];
                }
            } else {
                $orders = $order;
            }
            foreach ($orders as $params) {
                $builder->orderBy(...(is_array($params) ? $params : [$params, 'desc']));
            }
        }
        $items = $builder->paginate($perPage);
        if (!empty($query)) {
            $items->appends([$searchName => $query]);
        }

        return $items;
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