<?php

namespace Viviniko\Catalog\Repositories\Product;

use Viviniko\Repository\EloquentRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EloquentProduct extends EloquentRepository implements ProductRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('catalog.product'));
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestProducts($take, $columns = ['*'])
    {
        return $this->createQuery()->latest()->limit($take)->get($columns);
    }
}