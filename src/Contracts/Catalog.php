<?php

namespace Viviniko\Catalog\Contracts;

interface Catalog
{
    public function getCategory($id);

    public function getProduct($id);

    public function getProductSpecsByProductId($productId);

    public function getProductSpecGroupsByProductId($productId);

    public function getProductAttrsByProductId($productId);

    public function getAttrGroupsByCategoryId($categoryId);

    public function getProductItemsByProductId($productId);
}