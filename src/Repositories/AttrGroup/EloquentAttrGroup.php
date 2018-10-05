<?php

namespace Viviniko\Catalog\Repositories\AttrGroup;

use Viviniko\Repository\SimpleRepository;

class EloquentAttrGroup extends SimpleRepository implements AttrGroupRepository
{
    protected $modelConfigKey = 'catalog.attr_group';

    protected $fieldSearchable = [
        'name'
    ];
}