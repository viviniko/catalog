<?php

namespace Viviniko\Catalog\Observers;

use Viviniko\Catalog\Models\Item;
use Viviniko\Catalog\Repositories\Manufacturer\ManufacturerRepository;

class ItemObserver
{
    protected $manufacturers;

    public function __construct(ManufacturerRepository $manufacturers)
    {
        $this->manufacturers = $manufacturers;
    }

    public function saved(Item $item)
    {
        if ($item->is_primary) {
            $item->product->items
                ->where('is_primary', true)
                ->where('id', '!=', $item->id)
                ->each(function ($other) {
                    $other->update(['is_primary' => false]);
                });
            $item->product->searchable();
        }

        if ($mId = data_get($item->product->manufacturerProduct, 'manufacturer_id')) {
            $this->manufacturers->update($mId, ['product_update_time' => $item->product->updated_at]);
        }
    }

    public function deleted(Item $item)
    {

    }
}