<?php

namespace Viviniko\Catalog\Repositories\Specification;

use Illuminate\Support\Arr;
use Viviniko\Repository\SimpleRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EloquentSpecification extends SimpleRepository implements SpecificationRepository
{
    protected $modelConfigKey = 'catalog.specification';

    /**
     * {@inheritdoc}
     */
    public function getFilterableSpecificationsByCategoryId($categoryId)
    {
        $specificationGroupTableName = Config::get('catalog.specification_groups_table');
        $specificationTablesName = Config::get('catalog.specifications_table');
        $productTableName = Config::get('catalog.products_table');
        $productSpecificationTableName = Config::get('catalog.product_specification_table');

        return $this->createModel()->newQuery()->with('group')
            ->join($specificationGroupTableName, "{$specificationGroupTableName}.id", '=', "{$specificationTablesName}.group_id")
            ->where("{$specificationGroupTableName}.is_filterable", true)
            ->whereIn("{$specificationTablesName}.id", function ($query) use ($productSpecificationTableName, $categoryId, $productTableName) {
                $query->select('specification_id')->from($productSpecificationTableName)->whereIn('product_id', function ($subQuery) use ($categoryId, $productTableName) {
                    $subQuery->select('id')->from($productTableName)->whereIn('category_id', $categoryId instanceof Arrayable ? $categoryId : [$categoryId])->where('is_active', true);
                });
            })
            ->get(["{$specificationTablesName}.*"]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableSpecificationsByProductId($productId)
    {
        $specificationGroupTableName = Config::get('catalog.specification_groups_table');
        $productSpecificationTableName = Config::get('catalog.product_specification_table');
        $specificationTablesName = Config::get('catalog.specifications_table');
        return $this->createModel()->newQuery()->with('group')
            ->join($specificationGroupTableName, "{$specificationGroupTableName}.id", '=', "{$specificationTablesName}.group_id")
            ->where("{$specificationGroupTableName}.is_searchable", true)
            ->whereIn("{$specificationTablesName}.id", function ($query) use ($productSpecificationTableName, $productId) {
                $query->select('specification_id')->from($productSpecificationTableName)->where('product_id', $productId);
            })
            ->get(["{$specificationTablesName}.*"]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            DB::table(Config::get('catalog.product_specification_table'))->where('specification_id', $id)->delete();
            return parent::delete($id);
        });
    }
}