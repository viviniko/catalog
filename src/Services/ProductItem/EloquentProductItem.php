<?php

namespace Viviniko\Catalog\Services\ProductItem;

use Viviniko\Catalog\Contracts\ProductItemService as ProductItemServiceInterface;
use Viviniko\Repository\SimpleRepository;

class EloquentProductItem extends SimpleRepository implements ProductItemServiceInterface
{
    protected $modelConfigKey = 'catalog.product_item';
}