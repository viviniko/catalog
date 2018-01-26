<?php

namespace Viviniko\Catalog\Repositories\Attribute;

use Viviniko\Repository\SimpleRepository;

class EloquentAttribute extends SimpleRepository implements AttributeRepository
{
    protected $modelConfigKey = 'catalog.attribute';

    /**
     * {@inheritdoc}
     */
    public function findByGroupId($groupId)
    {
        return $this->findBy('group_id', $groupId);
    }

    /**
     * {@inheritdoc}
     */
    public function findIn($ids)
    {
        return $this->createModel()->newQuery()->whereIn('id', (array)$ids)->with('group')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function guessByName($name, $groupId = null)
    {
        $query = $this->createModel()->newQuery();
        if ($groupId) {
            $query->where('group_id', $groupId);
        }
        return $query->where('name', 'like', "%{$name}%")->first();
    }
}