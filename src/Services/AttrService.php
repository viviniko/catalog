<?php

namespace Viviniko\Catalog\Services;

interface AttrService
{
    public function find($id);

    /**
     * Get specification attributes by category id.
     *
     * @param $categoryId
     * @return mixed
     */
    public function getFilterableAttrsByCategoryId($categoryId);

    /**
     * Get specification attributes by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getSearchableAttrsByProductId($productId);

    /**
     * Get specification attributes by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getViewableAttrsByProductId($productId);
}