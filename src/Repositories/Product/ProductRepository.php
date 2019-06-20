<?php

namespace Viviniko\Catalog\Repositories\Product;

use Viviniko\Repository\CrudRepository;

interface ProductRepository extends CrudRepository
{
    /**
     * Get latest products.
     *
     * @param $take
     * @param $columns
     *
     * @return mixed
     */
    public function getLatestProducts($take, $columns = ['*']);
}