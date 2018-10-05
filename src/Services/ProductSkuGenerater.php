<?php

namespace Viviniko\Catalog\Services;

interface ProductSkuGenerater
{
    /**
     * @param $productId
     * @param array $options
     * @return string
     */
    public function generate($productId, array $options = []);
}