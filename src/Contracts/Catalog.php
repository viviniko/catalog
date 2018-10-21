<?php

namespace Viviniko\Catalog\Contracts;

interface Catalog
{
    public function getProductFilterableAttrGroupsByCategoryId($categoryId);

    public function getCategory($id);

    public function getProduct($id);
}