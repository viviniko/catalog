<?php

namespace Viviniko\Catalog\Repositories\Item;

use Viviniko\Repository\CrudRepository;

interface ItemRepository extends CrudRepository
{
    /**
     * @param $productId
     * @return mixed
     */
    public function findAllByProductId($productId);

    /**
     * @param $productId
     * @return mixed
     */
    public function findMasterByProductId($productId);
}