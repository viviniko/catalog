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
    public function createByAttributes($productId, array $attributes, array $data = [])
    {
        $item = null;
        DB::transaction(function () use ($productId, $attributes, $data, &$item) {
            $item = $this->create(array_merge([
                'product_id' => $productId,
                'price' => 0,
                'weight' => 0,
                'quantity' => 0,
                'is_active' => true,
                'is_master' => false,
            ], $data));

            $item->attributes()->attach($attributes);
        });

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        DB::transaction(function () use ($id) {
            $item = $this->find($id);
            $item->attributes()->sync([]);
            parent::delete($id);
        });
    }

}