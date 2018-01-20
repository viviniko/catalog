<?php

namespace Viviniko\Catalog\Repositories\Manufacturer;

use Viviniko\Repository\SimpleRepository;

class EloquentManufacturer extends SimpleRepository implements ManufacturerRepository
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

    /**
     * {@inheritdoc}
     */
    public function findByName($name)
    {
        return $this->createModel()->where('name', $name)->first();
    }
}