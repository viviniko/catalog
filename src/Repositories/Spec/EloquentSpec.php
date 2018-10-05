<?php

namespace Viviniko\Catalog\Repositories\Spec;

use Viviniko\Repository\SimpleRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EloquentSpec extends SimpleRepository implements SpecRepository
{
    protected $modelConfigKey = 'catalog.spec';

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