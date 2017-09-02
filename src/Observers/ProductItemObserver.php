<?php

namespace Viviniko\Catalog\Observers;

use Viviniko\Catalog\Contracts\ManufacturerService;
use Viviniko\Catalog\Models\ProductItem;

class ProductItemObserver
{
    protected $manufacturerService;

    public function __construct(ManufacturerService $manufacturerService)
    {
        $this->manufacturerService = $manufacturerService;
    }

    public function saved(ProductItem $productItem)
    {
        if ($productItem->is_master) {
            $productItem->product->searchable();
        }

        if ($mId = data_get($productItem->product->manufacturer, 'manufacturer_id')) {
            $this->manufacturerService->update($mId, ['product_update_time' => $productItem->product->updated_at]);
        }
    }
}