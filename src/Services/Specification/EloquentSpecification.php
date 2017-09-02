<?php

namespace Viviniko\Catalog\Services\Specification;

use Viviniko\Catalog\Contracts\SpecificationService as SpecificationServiceInterface;
use Viviniko\Repository\SimpleRepository;

class EloquentSpecification extends SimpleRepository implements SpecificationServiceInterface
{
    protected $modelConfigKey = 'catalog.specification';

    /**
     * Get specifications by all id given.
     *
     * @param $ids
     * @return mixed
     */
    public function findIn($ids)
    {
        return $this->createModel()->newQuery()->with('group')->whereIn('id', $ids)->get();
    }
}