<?php

namespace Viviniko\Catalog\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Viviniko\Catalog\Contracts\ProductSkuGenerater;
use Viviniko\Catalog\Repositories\Category\CategoryRepository;
use Viviniko\Catalog\Repositories\Product\ProductRepository;

class DefaultProductSkuGenerater implements ProductSkuGenerater
{
    protected $categoryRepository;

    protected $productRepository;

    public function __construct(CategoryRepository $categoryRepository, ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function generate($productId, array $options = [])
    {
        $product = $this->productRepository->find($productId);
        $category = $this->categoryRepository->find($product->category_id);

        $cids = array_filter(explode('/', $category->path));
        $prefix = '';
        if (count($cids) == 1) {
            $category = $this->categoryRepository->find($cids[0]);
            $prefix = substr($category->name, 0, 3);
        } else {
            foreach (array_slice($cids, 0, 2) as $i => $cid) {
                $category = $this->categoryRepository->find($cid);
                if ($i == 0) {
                    $prefix .= $category->name[0];
                } else {
                    $words = explode(' ', $category->name);
                    if (count($words) == 1) {
                        $prefix .= $category->name[0] . $category->name[1];
                    } else {
                        $prefix .= $words[0][0] . $words[1][0];
                    }
                }
            }
        }
        $carbon = Carbon::now();
        $time = ((string)$carbon->year)[3] . sprintf('%02d', $carbon->month);
        $number = (int)Cache::get('product-sku-number-' . $carbon->month);
        if (!$number) {
            $products = $this->productRepository->getLatestProducts(1);
            $number = 0;
            if ($products->isNotEmpty()) {
                if (strlen($products[0]->sku) == 10) {
                    $number = (int)substr($products[0]->sku, -4);
                } else if (strlen($products[0]->sku) == 14) {
                    $number = (int)substr($products[0]->sku, -6, 4);
                }
            }
        }
        ++$number;
        $number = sprintf('%04d', $number);
        Cache::put('product-sku-number-' . $carbon->month, $number, $carbon->addMonth());

        return strtoupper(($options['prefix'] ?? '') . $prefix . $time . $number . ($options['suffix'] ?? ''));
    }
}