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
use Viviniko\Media\Contracts\ImageService;

class ProductServiceImpl implements ProductService
{
    protected $productRepository;

    protected $itemService;

    protected $imageService;

    public function __construct(
        ProductRepository $productRepository,
        ItemService $itemService,
        ImageService $imageService
    )
    {
        $this->productRepository = $productRepository;
        $this->itemService = $itemService;
        $this->imageService = $imageService;
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

    /**
     * {@inheritdoc}
     */
    public function attachAttributeGroups($productId, array $data)
    {
        foreach ($data as $groupId => $attributes) {
            $this->productRepository->attachProductAttributeGroup($productId, $groupId, $attributes);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateAttributeGroups($productId, array $data)
    {
        foreach ($data as $groupId => $attributes) {
            $this->productRepository->updateProductAttributeGroup($productId, $groupId, $attributes);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detachAttributeGroup($productId, $groupId)
    {
        $this->productRepository->detachProductAttributeGroup($productId, $groupId);
    }

    /**
     * {@inheritdoc}
     */
    public function attachAttribute($productId, array $data)
    {
        $product = $this->productRepository->find($productId);
        foreach ($data as $attributeId => $attributes) {
            DB::transaction(function () use ($product, $attributes, $productId, $attributeId) {
                if (isset($attributes['is_selected']) && $attributes['is_selected']) {
                    $this->productRepository->resetProductSelectedAttribute($productId, $attributeId);
                }
                $this->addProductAttributeSwatchPicture($attributes);
                $product->attributes()->attach($attributeId, $attributes);
            });
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function updateAttribute($productId, array $data)
    {
        $product = $this->productRepository->find($productId);
        foreach ($data as $attributeId => $attributes) {
            DB::transaction(function () use ($product, $attributes, $productId, $attributeId) {
                if (isset($attributes['is_selected']) && $attributes['is_selected']) {
                    $this->productRepository->resetProductSelectedAttribute($productId, $attributeId);
                }
                $this->addProductAttributeSwatchPicture($attributes);
                $product->attributes()->updateExistingPivot($attributeId, $attributes);
            });
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function detachAttribute($productId, $attributeId)
    {
        $product = $this->productRepository->find($productId);
        $product->attributes()->detach($attributeId);

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function addProductAttributeSwatchPicture(array &$attributes, $x = null, $y = null)
    {
        $size = config('catalog.settings.swatch_picture_size', 60);
        if (isset($attributes['picture_id']) && $attributes['picture_id'] != 0 && !isset($attributes['swatch_picture_id'])) {
            $picture = $this->imageService->crop($attributes['picture_id'], $size, $size, $x, $y);

            $attributes['swatch_picture_id'] = $picture->id;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateProductAttributeSwatchPicture($productId, $attributeId, $pictureId, $x, $y)
    {
        $product = $this->productRepository->find($productId);
        if ($product) {
            $attributes = ['picture_id' => $pictureId];
            $this->addProductAttributeSwatchPicture($attributes, $x, $y);

            return $product->attributes()->updateExistingPivot($attributeId, $attributes);
        }

        return false;
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