<?php

namespace Viviniko\Catalog\Contracts;

interface SpecificationService
{
    public function find($id);

    /**
     * Get specification attributes by category id.
     *
     * @param $categoryId
     * @return mixed
     */
    public function getFilterableSpecificationsByCategoryId($categoryId);

    /**
     * Get specification attributes by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getSearchableSpecificationsByProductId($productId);

    /**
     * Get specification attributes by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getViewableSpecificationsByProductId($productId);
}