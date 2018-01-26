<?php

namespace Viviniko\Catalog\Services\Product;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Viviniko\Catalog\Contracts\ItemService;
use Viviniko\Catalog\Contracts\ProductService;
use Viviniko\Catalog\Models\Product;
use Viviniko\Catalog\Repositories\Product\ProductRepository;

class ProductServiceImpl implements ProductService
{
    protected $productRepository;

    protected $itemService;

    public function __construct(ProductRepository $productRepository, ItemService $itemService)
    {
        $this->productRepository = $productRepository;
        $this->itemService = $itemService;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return collect($id)->map(function ($item) {
                return $this->find($item);
            });
        }

        return Cache::tags('catalog.products')->remember("catalog.product?:{$id}", Config::get('cache.ttl', 10), function () use ($id) {
            return $this->productRepository->find($id);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = $this->productRepository->create($data);
            $product->items()->updateOrCreate(['product_id' => $product->id, 'is_master' => true], $data);
            $this->syncProductData($product, $data);

            return $product;
        });
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $product = $this->productRepository->update($id, $data);
            $this->syncProductData($product, $data);

            return $product;
        });

    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            $product = $this->find($id);
            if ($product) {
                $this->itemService->deleteByProductId($product->id);
                $product->pictures()->sync([]);
                $product->specifications()->sync([]);
                $product->attributeGroups()->sync([]);
                $product->attributes()->sync([]);
                DB::table(Config::get('catalog.manufacturer_products_table'))->where('product_id', $product->id)->delete();

                return $this->productRepository->delete($id);
            }
        });
    }

    protected function syncProductData(Product $product, array $data)
    {
        if (!empty($data['specifications'])) {
            $product->specifications()->sync($data['specifications']);
        }

        if (!empty($data['pictures'])) {
            $product->pictures()->sync($data['pictures']);
            foreach ($data['pictures'] as $i => $picture) {
                $product->pictures()->updateExistingPivot($picture, ['sort' => $i]);
            }
        }

        if (!empty($data['manufacturer_product'])) {
            $product->manufacturerProduct()->updateOrCreate([
                'product_id' => $product->id,
                'manufacturer_id' => $data['manufacturer_product']['manufacturer_id'],
            ], $data['manufacturer_product']);
        }
    }
}