<?php

namespace Viviniko\Catalog\Services\Impl;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Viviniko\Catalog\Services\AttrService;
use Viviniko\Catalog\Services\CategoryService;
use Viviniko\Catalog\Services\ItemService;
use Viviniko\Catalog\Services\ProductService;
use Viviniko\Catalog\Services\SpecificationService;
use Viviniko\Catalog\Models\Product;
use Viviniko\Catalog\Repositories\Product\ProductRepository;
use Viviniko\Media\Contracts\ImageService;

class ProductServiceImpl implements ProductService
{
    use ProductSearchableTrait;

    /**
     * @var \Viviniko\Catalog\Repositories\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Viviniko\Catalog\Services\ItemService
     */
    protected $itemService;

    /**
     * @var \Viviniko\Catalog\Services\CategoryService
     */
    protected $categoryService;

    /**
     * @var \Viviniko\Catalog\Services\AttrService
     */
    protected $attributeService;

    /**
     * @var \Viviniko\Catalog\Services\SpecificationService
     */
    protected $specificationService;

    /**
     * @var \Viviniko\Media\Contracts\ImageService
     */
    protected $imageService;

    public function __construct(
        ProductRepository $productRepository,
        ItemService $itemService,
        CategoryService $categoryService,
        AttrService $attributeService,
        SpecificationService $specificationService,
        ImageService $imageService
    )
    {
        $this->productRepository = $productRepository;
        $this->itemService = $itemService;
        $this->imageService = $imageService;
        $this->categoryService = $categoryService;
        $this->attributeService = $attributeService;
        $this->specificationService = $specificationService;
    }

    /**
     * {@inheritdoc}
     */
    public function search($keyword = null, $filters = null, $order = null, $except = null, $categories = [])
    {
        $builder = $this->makeSearchBuilder($keyword, $filters, $except);

        if (empty($order)) {
            $order = 'recommend_score';
        } else if (is_string($order)) {
            if ($order == 'recommend') {
                $order = 'recommend_score';
            } else if ($order == 'hot') {
                $order = 'quarter_sold_count';
            } else if ($order == 'new') {
                $order = 'created_at';
            } else if ($order == 'high_price') {
                $order = 'price';
            } else if ($order == 'low_price') {
                $order = ['price', 'asc'];
            } else if ($order == 'score') {
                $order = ['_score', 'desc'];
            } else {
                $order = [$order ?? 'sort', 'desc'];
            }
        }

        if (!empty($order)) {
            if (!is_array($order)) {
                $order = [$order, 'desc'];
            }
            $builder->orderBy(...$order);
            if ($order[0] == 'recommend_score') {
                $builder->orderBy('sort', 'desc');
            }
        } else {
            $builder->orderBy('sort', 'desc');
            $builder->orderBy('created_at', 'desc');
        }

        return $builder;
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
                $product->specs()->sync([]);
                $product->specGroups()->sync([]);
                $product->attrs()->sync([]);
                DB::table(Config::get('catalog.manufacturer_products_table'))->where('product_id', $product->id)->delete();

                return $this->productRepository->delete($id);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function attachSpecGroups($productId, array $data)
    {
        foreach ($data as $groupId => $attributes) {
            $this->productRepository->attachProductSpecGroup($productId, $groupId, $attributes);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateSpecGroups($productId, array $data)
    {
        foreach ($data as $groupId => $attributes) {
            $this->productRepository->updateProductSpecGroup($productId, $groupId, $attributes);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detachSpecGroup($productId, $groupId)
    {
        $this->productRepository->detachProductSpecGroup($productId, $groupId);
    }

    /**
     * {@inheritdoc}
     */
    public function attachSpec($productId, array $data)
    {
        $product = $this->productRepository->find($productId);
        foreach ($data as $attributeId => $attributes) {
            DB::transaction(function () use ($product, $attributes, $productId, $attributeId) {
                if (isset($attributes['is_selected']) && $attributes['is_selected']) {
                    $this->productRepository->resetProductSelectedSpec($productId, $attributeId);
                }
                $this->addProductSpecSwatchPicture($attributes);
                $product->specs()->attach($attributeId, $attributes);
            });
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function updateSpec($productId, array $data)
    {
        $product = $this->productRepository->find($productId);
        foreach ($data as $attributeId => $attributes) {
            DB::transaction(function () use ($product, $attributes, $productId, $attributeId) {
                if (isset($attributes['is_selected']) && $attributes['is_selected']) {
                    $this->productRepository->resetProductSelectedSpec($productId, $attributeId);
                }
                $this->addProductSpecSwatchPicture($attributes);
                $product->specs()->updateExistingPivot($attributeId, $attributes);
            });
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function detachSpec($productId, $attributeId)
    {
        $product = $this->productRepository->find($productId);
        $product->specs()->detach($attributeId);

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function detachProductPicture($pictureId)
    {
        DB::table(config('catalog.product_picture_table'))->where('picture_id', $pictureId)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function addProductSpecSwatchPicture(array &$attributes, $x = null, $y = null)
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
    public function updateProductSpecSwatchPicture($productId, $attributeId, $pictureId, $x, $y)
    {
        $product = $this->productRepository->find($productId);
        if ($product) {
            $attributes = ['picture_id' => $pictureId];
            $this->addProductSpecSwatchPicture($attributes, $x, $y);
            $product->attrs()->updateExistingPivot($attributeId, $attributes);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductSwatchPictures($productId)
    {
        return $this->productRepository->getProductSpec($productId)
            ->filter(function ($item) { return !empty($item->swatch_picture_id);})
            ->sortBy('sort')
            ->map(function ($item) {
                $swatch = new \stdClass();
                $swatch->swatch_picture_url = $this->imageService->getUrl($item->swatch_picture_id);
                $swatch->picture_url = $this->imageService->getUrl($item->picture_id);
                $swatch->spec_id = $item->spec_id;
                $swatch->swatch_picture_name = data_get($this->specificationService->find($item->spec_id), 'title');
                return $swatch;
            });
    }

    /**
     * {@inheritdoc}
     */
    public function generateProductItems($productId)
    {
        $product = $this->productRepository->find($productId);
        $productItems = $product->items->all();
        $items = collect([]);
        $comAttrs = Arr::crossJoin(...$product->specs->groupBy('group_id')->map(function ($item) { return $item->all(); }));

        foreach ($comAttrs as $comAttr) {
            $attributes = array_map(function ($item) { return $item->id; }, $comAttr);
            foreach ($productItems as $key => $productItem) {
                $productItemAttributes = $productItem->specs->pluck('id')->all();
                if (count($attributes) == count($productItemAttributes) && empty(array_diff($attributes, $productItemAttributes))) {
                    $items[] = $productItem;
                    unset($productItems[$key]);
                    continue 2;
                }
            }
            $items->push($this->itemService->createBySpecs($product->id, $attributes));
        }

        if ($items->filter(function ($item) { return $item->is_master; })->isEmpty()) {
            $this->itemService->update($items[0]->id, ['is_master' => true]);
        }

        if (!empty($productItems)) {
            foreach ($productItems as $productItem) {
                $this->itemService->delete($productItem->id);
            }
        }

        return $items;
    }

    protected function syncProductData(Product $product, array $data)
    {
        if (!empty($data['attrs'])) {
            $product->attrs()->sync($data['attrs']);
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

        if (!empty($data['tags'])) {
            $product->tags()->sync($data['tags']);
        }
    }

    protected function makeSearchBuilder($keyword = null, $filters = null, $except = null, $fields = null)
    {
        if (!empty($keyword)) {
            $keyword = str_replace(['{', '}', '*', '[', ']', '(', ')', '!', '&', '^', '"', '\\', ':', '/'], '', $keyword);
        }

        $builder = Config::get('catalog.product')::search($keyword);

        if (!empty($filters)) {
            foreach ($filters as $name => $value) {
                if (empty($value)) continue;
                if ($name == 'category_id') {
                    $builder->where('category_id', $this->categoryService->getChildrenId($value)->prepend($value)->toArray());
                } else if ($name == 'attrs') {
                    foreach ($value as $key=>$val){
                        if (is_array($val)) {
                            $builder->where('attrs:'.$key,$val);
                        } else {
                            $builder->where('term.attrs:'.$key,array_unique((array)$val));
                        }
                    }
                } else if ($name == 'tags' || $name == 'term.tags') {
                    $builder->where($name, array_unique(array_filter((array)$value)));
                } else {
                    $builder->where($name, $value);
                }
            }
        }

        $builder->where('is_active', true);

        if ($except) {
            $mustNot = [];
            foreach ($except as $key => $values) {
                $values = array_unique(array_values((array) $values));
                if (count($values) == 1) {
                    $mustNot['term'] = [$key => $values[0]];
                } else {
                    $mustNot['terms'] = [$key => $values];
                }
            }

            $builder->rawFilters = [
                'bool' => [
                    'must_not' => $mustNot,
                ],
            ];
        }

        if ($fields) {
            $builder->fields = $fields;
        }

        return $builder;
    }
}