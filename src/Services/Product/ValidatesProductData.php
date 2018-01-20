<?php

namespace Common\Catalog\Services\Product;

use Common\Repository\ValidatesData;
use Illuminate\Support\Facades\Config;

trait ValidatesProductData
{
    use ValidatesData;

    public function validateCreateData($data)
    {
        $this->validate($data, $this->rules());
        $this->validateSku($data['sku']);
    }

    public function validateUpdateData($productId, $data)
    {
        $rules = $this->rules($productId);
        $this->validate($data, $rules);
        $this->validateSku($data['sku'], data_get($this->find($productId)->master, 'id'));
    }

    public function validateSku($sku, $productItemId = null)
    {
        $productItemId = $productItemId ? (',' . $productItemId) : '';
        $productItemTable = Config::get('catalog.product_items_table');
        $this->validate(['sku' => $sku], [
            'sku' => 'required|unique:' . $productItemTable . ',sku' . $productItemId,
        ]);
    }

    public function rules($productId = null)
    {
        return [
            'name' => 'required|max:255',
            'description' => 'required',
            'price' => 'required|numeric',
            'weight' => 'required|numeric',
            'purchasing_price' => 'required|numeric',
            'market_price' => 'required|numeric',
            'product_origin_sku' => 'max:32',
        ];
    }
}