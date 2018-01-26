<?php

namespace Viviniko\Catalog\Repositories\Product;

use Viviniko\Media\Contracts\ImageService;
use Viviniko\Repository\SimpleRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EloquentProduct extends SimpleRepository implements ProductRepository
{
    protected $modelConfigKey = 'catalog.product';

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage = null, $searchName = 'search', $search = null)
    {
        $productTable = Config::get('catalog.products_table');
        $productManufacturerTable = Config::get('catalog.manufacturer_products_table');
        $productItemsTable = Config::get('catalog.items_table');
        $manufacturerTable = Config::get('catalog.manufacturers_table');
        $categoryTable = Config::get('catalog.categories_table');
        $taggablesTable = Config::get('tag.taggables_table');

        $this->fieldSearchable = [
            'id' => "{$productTable}.id:=",
            'name' => "{$productTable}.name:like",
            'category' => "{$categoryTable}.id:=",
            'sku' => 'like',
            'manufacturer' => "{$manufacturerTable}.id:=",
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

        $builder = parent::search($search)->select(["{$productTable}.*"])
            ->join($categoryTable, "{$productTable}.category_id", '=', "{$categoryTable}.id", 'left')
            ->join($productManufacturerTable, "{$productTable}.id", '=', "{$productManufacturerTable}.product_id", 'left')
            ->join($manufacturerTable, "{$manufacturerTable}.id", '=', "{$productManufacturerTable}.manufacturer_id", 'left')
            ->join($productItemsTable, "{$productTable}.id", '=', "{$productItemsTable}.product_id", 'left')
            ->where("{$productItemsTable}.is_master", true);
        if (isset($search['has_tag'])) {
            if ($search['has_tag'] == '1') {
                $builder->has('tags');
            } else {
                $builder->doesntHave('tags');
            }
        }
        if (isset($search['tags'])) {
            $builder->join("$taggablesTable", "{$productTable}.id", '=', "{$taggablesTable}.taggable_id", 'left');
            $builder->whereIn("{$taggablesTable}.tag_id", $search['tags']);
        }

        $builder->orderBy('created_at', 'desc');

        $result = $builder->paginate($perPage);

        if (!empty($search)) {
            $query = [];
            foreach($search as $key => $value) {
                $query[$searchName][$key] = $value;
            }

            $result->appends($query);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestProducts($take, $columns = ['*'])
    {
        return $this->createModel()->latest()->limit($take)->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function attachProductAttributeGroup($productId, $attributeGroupId, array $attributes = [])
    {
        if ($product = $this->find($productId)) {
            $product->attributeGroups()->attach($attributeGroupId, $attributes);
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function updateProductAttributeGroup($productId, $attributeGroupId, array $attributes = [])
    {
        if ($product = $this->find($productId)) {
            $product->attributeGroups()->updateExistingPivot($attributeGroupId, $attributes);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detachProductAttributeGroup($productId, $groupId)
    {
        if ($product = $this->find($productId)) {
            $product->attributeGroups()->detach($groupId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resetProductSelectedAttribute($productId, $attributeId)
    {
        DB::table(Config::get('catalog.product_attribute_table'))
            ->where('product_id', $productId)
            ->where('is_selected', 1)
            ->whereIn('attribute_id', function ($query) use ($attributeId) {
                $query->select('id')->from(Config::get('catalog.attributes_table'))->where('group_id', function ($subQuery) use ($attributeId) {
                    $subQuery->select('group_id')->from(Config::get('catalog.attributes_table'))->where('id', $attributeId);
                });
            })->update(['is_selected' => 0]);
    }
}