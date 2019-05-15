<?php

namespace Viviniko\Catalog\Repositories\AttrValue;

use Viviniko\Repository\CrudRepository;

interface AttrValueRepository extends CrudRepository
{
    /**
     * Get filterable attributes by given categories.
     *
     * @param mixed $categoryId
     * @return mixed
     */
    public function getFilterableAttrsByCategoryId($categoryId);

    /**
     * Get searchable attributes by given product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getSearchableAttrsByProductId($productId);

    /**
     * Get viewable attributes by given product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getViewableAttrsByProductId($productId);
}