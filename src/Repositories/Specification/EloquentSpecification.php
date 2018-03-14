<?php

namespace Viviniko\Catalog\Repositories\Specification;

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
        $productTableName = Config::get('catalog.products_table');
        return $this->getSpecificationsByProductId(function ($query) use ($categoryId, $productTableName) {
            $query->select('id')->from($productTableName)->whereIn('category_id', is_array($categoryId) || $categoryId instanceof Arrayable ? $categoryId : [$categoryId])->where('is_active', true);
        }, 'is_filterable');
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableSpecificationsByProductId($productId)
    {
        return $this->getSpecificationsByProductId($productId, 'is_searchable');
    }

    /**
     * {@inheritdoc}
     */
    public function getViewableSpecificationsByProductId($productId)
    {
        return $this->getSpecificationsByProductId($productId, 'is_viewable');
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

    private function getSpecificationsByProductId($productId, $which)
    {
        $specificationGroupTableName = Config::get('catalog.specification_groups_table');
        $productSpecificationTableName = Config::get('catalog.product_specification_table');
        $specificationTablesName = Config::get('catalog.specifications_table');
        return $this->createModel()->newQuery()->with('group')
            ->join($specificationGroupTableName, "{$specificationGroupTableName}.id", '=', "{$specificationTablesName}.group_id")
            ->where("{$specificationGroupTableName}.{$which}", true)
            ->whereIn("{$specificationTablesName}.id", function ($query) use ($productSpecificationTableName, $productId) {
                $query = $query->select('specification_id')->from($productSpecificationTableName);
                if (is_callable($productId)) {
                    $query->whereIn('product_id', $productId);
                } else {
                    $query->where('product_id', $productId);
                }

            })
            ->get(["{$specificationTablesName}.*"]);
    }
}