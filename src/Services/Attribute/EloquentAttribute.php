<?php

namespace Viviniko\Catalog\Services\Attribute;

use Viviniko\Catalog\Contracts\AttributeService as AttributeServiceInterface;
use Viviniko\Repository\SimpleRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

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

    /**
     * Get attribute by name like the given name
     *
     * @param $name
     * @param null $groupId
     * @return mixed
     */
    public function guessByName($name, $groupId = null)
    {
        return Cache::remember("catalog.attribute.guess-name?:{$name}", Config::get('cache.ttl', 10), function () use ($name, $groupId) {
            $query =  $this->createModel()->newQuery();
            if ($groupId) {
                $query->where('group_id', $groupId);
            }
            return $query->where('name', 'like', "%{$name}%")->first();
        });
    }
}