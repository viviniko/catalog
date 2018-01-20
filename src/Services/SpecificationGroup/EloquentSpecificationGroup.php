<?php

namespace Viviniko\Catalog\Services\SpecificationGroup;

use Viviniko\Catalog\Contracts\SpecificationGroupService as SpecificationGroupServiceInterface;
use Viviniko\Repository\SimpleRepository;

class EloquentSpecificationGroup extends SimpleRepository implements SpecificationGroupServiceInterface
{
    protected $modelConfigKey = 'catalog.specification_group';

    /**
     * Get attributes by category id.
     *
     * @param $categoryId
     * @return mixed
     */
    public function findByCategoryId($categoryId)
    {
        return $this->createModel()->newQuery()->with('specifications')->whereIn('category_id', (array) $categoryId)->get();
    }
}