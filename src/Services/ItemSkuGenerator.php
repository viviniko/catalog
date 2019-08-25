<?php

namespace Viviniko\Catalog\Services;

interface ItemSkuGenerator
{
    /**
     * @param $productId
     * @param array $options
     * @return string
     */
    public function generate($itemId, array $options = []);
}