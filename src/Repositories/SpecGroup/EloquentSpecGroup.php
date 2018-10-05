<?php

namespace Viviniko\Catalog\Repositories\SpecGroup;

use Viviniko\Repository\SimpleRepository;

class EloquentSpecGroup extends SimpleRepository implements SpecGroupRepository
{
    protected $modelConfigKey = 'catalog.spec_group';

    public function all($columns = ['*'])
    {
        return $this->search([])->get($columns);
    }
}