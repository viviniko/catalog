<?php

namespace Viviniko\Catalog\Services\Attribute;

use Viviniko\Catalog\Contracts\AttributeService as AttributeServiceInterface;
use Viviniko\Repository\SimpleRepository;

class EloquentAttribute extends SimpleRepository implements AttributeServiceInterface
{
    protected $modelConfigKey = 'catalog.attribute';

    public function findByGroupId($groupId)
    {
        return $this->findBy('group_id', $groupId);
    }

    /**
     * Get attributes by all id given.
     *
     * @param $ids
     * @return mixed
     */
    public function findIn($ids)
    {
        return $this->createModel()->newQuery()->whereIn('id', (array)$ids)->with('group')->get();
    }
}