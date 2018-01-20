<?php

namespace Viviniko\Catalog\Contracts;

interface SpecificationService
{
    /**
     * Get specification attributes by category id.
     *
     * @param $categoryId
     * @return mixed
     */
    public function getFilterableSpecificationsByCategoryId($categoryId);
}