<?php

namespace Viviniko\Catalog\Repositories\AttributeGroup;

use Viviniko\Repository\SimpleRepository;

class EloquentAttributeGroup extends SimpleRepository implements AttributeGroupRepository
{
    protected $modelConfigKey = 'catalog.attribute_group';

    protected $fieldSearchable = [
        'name'
    ];

    public function all()
    {
        return $this->search([])->get();
    }

    public function lists()
    {
        return $this->pluck('name', 'id');
    }
}