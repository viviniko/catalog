<?php

namespace Viviniko\Catalog\Services\AttributeGroup;

use Viviniko\Catalog\Contracts\AttributeGroupService as AttributeGroupServiceInterface;
use Viviniko\Repository\SimpleRepository;

class EloquentAttributeGroup extends SimpleRepository implements AttributeGroupServiceInterface
{
    protected $modelConfigKey = 'catalog.attribute_group';

    public function all()
    {
        return $this->search([])->get();
    }

    public function lists()
    {
        return $this->pluck('name', 'id');
    }
}