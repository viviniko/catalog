<?php

namespace Viviniko\Catalog\Repositories\Spec;

use Illuminate\Support\Facades\Config;
use Viviniko\Repository\EloquentRepository;

class EloquentSpec extends EloquentRepository implements SpecRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('catalog.spec'));
    }

    /**
     * {@inheritdoc}
     */
    public function findIn($ids)
    {
        return $this->createQuery()->whereIn('id', (array)$ids)->with('group')->get();
    }

    /**
     * {@inheritdoc}
     */
    public function guessByName($name, $groupId = null)
    {
        $query = $this->createQuery();
        if ($groupId) {
            $query->where('group_id', $groupId);
        }
        return $query->where('name', 'like', "%{$name}%")->first();
    }
}