<?php

namespace Viviniko\Catalog\Services\Manufacturer;

use Viviniko\Catalog\Contracts\ManufacturerService as ManufacturerServiceInterface;
use Viviniko\Repository\SimpleRepository;

class EloquentManufacturer extends SimpleRepository implements ManufacturerServiceInterface
{
    protected $modelConfigKey = 'catalog.manufacturer';

    protected $fieldSearchable = [
        'id',
        'name' => "like",
        'product_type' => 'like',
        'product_update_period' => 'like',
        'product_update_time' => 'betweenDate',
        'is_active',
        'admin' => 'like'
    ];
}