<?php

namespace Viviniko\Catalog\Repositories\Item;

use Viviniko\Repository\SimpleRepository;

class EloquentItem extends SimpleRepository implements ItemRepository
{
    protected $modelConfigKey = 'catalog.item';

    protected $fieldSearchable = ['sku', 'is_master'];

}