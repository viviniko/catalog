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
    public function getFilterableSpecifications($id)
    {
        $specificationGroupTableName = Config::get('catalog.specification_groups_table');
        $specificationTablesName = Config::get('catalog.specifications_table');
        return $this->createModel()->newQuery()->with('group')
            ->join($specificationGroupTableName, "{$specificationGroupTableName}.id", '=', "{$specificationTablesName}.group_id")
            ->where("{$specificationGroupTableName}.is_filterable", true)
            ->whereIn("{$specificationTablesName}.id", is_array($id) || $id instanceof Arrayable ? $id : [$id])
            ->get(["{$specificationTablesName}.*"]);
    }

    /**
     * {@inheritdoc}
     */
    public function findInWithGroup($ids)
    {
        return $this->createModel()->newQuery()->whereIn('id', $ids)->with('group')->get();
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