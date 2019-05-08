<?php

namespace Viviniko\Catalog\Repositories\AttrValue;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Viviniko\Repository\EloquentRepository;

class EloquentAttrValue extends EloquentRepository implements AttrValueRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('catalog.attr_value'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterableAttrsByCategoryId($categoryId)
    {
        $productTableName = Config::get('catalog.products_table');
        return $this->getAttrsByProductId(function ($query) use ($categoryId, $productTableName) {
            $query->select('id')->from($productTableName)->whereIn('category_id', is_array($categoryId) || $categoryId instanceof Arrayable ? $categoryId : [$categoryId])->where('is_active', true);
        }, 'is_filterable');
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableAttrsByProductId($productId)
    {
        return $this->getAttrsByProductId($productId, 'is_searchable');
    }

    /**
     * {@inheritdoc}
     */
    public function getViewableAttrsByProductId($productId)
    {
        return $this->getAttrsByProductId($productId, 'is_viewable');
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            DB::table(Config::get('catalog.product_attr_table'))->where('attr_id', $id)->delete();
            return parent::delete($id);
        });
    }

    private function getAttrsByProductId($productId, $which)
    {
        $attributeGroupTableName = Config::get('catalog.attr_groups_table');
        $productAttributeTableName = Config::get('catalog.product_attr_table');
        $attributeTablesName = Config::get('catalog.attrs_table');
        return $this->createQuery()->with('group')
            ->join($attributeGroupTableName, "{$attributeGroupTableName}.id", '=', "{$attributeTablesName}.group_id")
            ->where("{$attributeGroupTableName}.{$which}", true)
            ->whereIn("{$attributeTablesName}.id", function ($query) use ($productAttributeTableName, $productId) {
                $query = $query->select('attr_id')->from($productAttributeTableName);
                if (is_callable($productId)) {
                    $query->whereIn('product_id', $productId);
                } else {
                    $query->where('product_id', $productId);
                }

            })
            ->get(["{$attributeTablesName}.*"]);
    }
}