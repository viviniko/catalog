<?php

namespace Viviniko\Catalog\Repositories\ProductItem;

use Viviniko\Repository\SimpleRepository;

class EloquentProductItem extends SimpleRepository implements ProductItemRepository
{
    protected $modelConfigKey = 'catalog.product_item';

    protected $fieldSearchable = ['sku', 'is_master'];

}