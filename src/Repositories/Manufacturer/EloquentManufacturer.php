<?php

namespace Viviniko\Catalog\Repositories\Manufacturer;

use Illuminate\Support\Facades\Config;
use Viviniko\Repository\EloquentRepository;

class EloquentManufacturer extends EloquentRepository implements ManufacturerRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('catalog.manufacturer'));
    }

    /**
     * {@inheritdoc}
     */
    public function findByName($name)
    {
        return $this->createQuery()->where('name', $name)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function lists($column = 'name', $key = null)
    {
        return $this->pluck($column, $key);
    }
}