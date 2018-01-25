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
        if ($item->is_master) {
            $item->product->searchable();
        }

        if ($mId = data_get($item->product->manufacturerProduct, 'manufacturer_id')) {
            $this->manufacturers->update($mId, ['product_update_time' => $item->product->updated_at]);
        }
    }
}