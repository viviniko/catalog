<?php

namespace Viviniko\Catalog\Repositories\Item;

use Illuminate\Support\Facades\DB;
use Viviniko\Repository\SimpleRepository;

class EloquentItem extends SimpleRepository implements ItemRepository
{
    protected $modelConfigKey = 'catalog.item';

    protected $fieldSearchable = ['sku', 'is_master'];

    /**
     * {@inheritdoc}
     */
    public function findByProductId($productId)
    {
        return $this->findBy('product_id', $productId);
    }
}