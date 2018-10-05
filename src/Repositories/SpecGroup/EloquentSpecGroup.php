<?php

namespace Viviniko\Catalog\Repositories\SpecGroup;

use Viviniko\Repository\SimpleRepository;

class EloquentSpecGroup extends SimpleRepository implements SpecGroupRepository
{
    protected $modelConfigKey = 'catalog.spec_group';

    /**
     * {@inheritdoc}
     */
    public function findByCategoryId($categoryId, $columns = ['*'])
    {
        return $this->createModel()->newQuery()->with('specs')->whereIn('category_id', (array) $categoryId)->get($columns);
    }
}