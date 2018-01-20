<?php

namespace Viviniko\Catalog\Repositories\SpecificationGroup;

use Viviniko\Repository\SimpleRepository;

class EloquentSpecificationGroup extends SimpleRepository implements SpecificationGroupRepository
{
    protected $modelConfigKey = 'catalog.specification_group';

    /**
     * {@inheritdoc}
     */
    public function findByCategoryId($categoryId, $columns = ['*'])
    {
        return $this->createModel()->newQuery()->with('specifications')->whereIn('category_id', (array) $categoryId)->get($columns);
    }
}