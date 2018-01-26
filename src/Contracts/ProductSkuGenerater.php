<?php

namespace Viviniko\Catalog\Contracts;

interface ProductSkuGenerater
{
    /**
     * @param $productId
     * @param array $options
     * @return string
     */
    public function generate($productId, array $options = []);
}