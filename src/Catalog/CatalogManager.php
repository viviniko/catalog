<?php

namespace Viviniko\Catalog\Catalog;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Viviniko\Catalog\Contracts\Catalog;
use Viviniko\Currency\Services\CurrencyService;
use Viviniko\Media\Services\ImageService;
use Viviniko\Repository\SimpleRepository;

class CatalogManager implements Catalog
{
    protected $imageService;

    protected $currencyService;

    protected $products;

    protected $categories;

    protected $specs;

    protected $productSpecs;

    protected $productSpecGroups;

    protected $specGroups;

    protected $productAttrs;

    protected $attrs;

    protected $attrGroups;

    protected $items;

    protected $itemSpecs;

    protected $productPictures;

    protected $configurations;

    protected $cacheMinutes = 5;

    public function __construct(ImageService $imageService, CurrencyService $currencyService)
    {
        $this->imageService = $imageService;
        $this->currencyService = $currencyService;
    }

    public function getCategoryChildrenIdByCategoryId($categoryId)
    {
        return Cache::remember("catalog.category.children:{$categoryId}", Config::get('cache.ttl', $this->cacheMinutes), function () use ($categoryId) {
            $children = collect([]);

            foreach ($this->getCategoryRepository()->findAllBy('parent_id', $categoryId, ['id']) as $category) {
                $children->push($category->id);
                $children = $children->merge($this->getCategoryChildrenIdByCategoryId($category->id));
            }

            return $children;
        });
    }

    /**
     * 获取给出分类及子分类所设置的属性
     *
     * @param $categoryId
     * @return mixed
     */
    public function getProductFilterableAttrGroupsByCategoryId($categoryId)
    {
        $attrGroups = collect([]);

        DB::table($this->getAttrRepository()->getTable())->whereIn('id', function ($query) use ($categoryId) {
            $query
                ->select('attr_id')
                ->from($this->getProductAttrRepository()->getTable())
                ->whereIn('product_id', function ($subQuery) use ($categoryId) {
                    $subQuery
                        ->select('id')
                        ->from($this->getProductRepository()->getTable())
                        ->where('is_active', 1)
                        ->whereIn('category_id', $this->getCategoryChildrenIdByCategoryId($categoryId)->prepend($categoryId));
                });
        })->pluck(['id'])->map(function ($attrId) use ($attrGroups) {
            $attr = $this->getAttr($attrId);
            if (!isset($attrGroups[$attr->group_id])) {
                $attrGroups[$attr->group_id] = $this->getAttrGroup($attr->group_id);
                $attrGroups[$attr->group_id]->attrs = collect([]);
            }
            $attrGroups[$attr->group_id]->attrs->push($attr);
        });

        return $attrGroups->filter(function ($attrGroup) {
            return $attrGroup->is_filterable;
        })->map(function ($attrGroup) {
            $attrGroup->attrs->sortBy('sort');
            return $attrGroup;
        })->sortBy('sort');
    }

    public function getAttrGroup($id)
    {
        return Cache::remember("catalog.attr_group:{$id}", Config::get('cache.ttl', $this->cacheMinutes), function () use ($id) {
            return $this->getAttrGroupRepository()->find($id);
        });
    }

    public function getAttr($id)
    {
        return Cache::remember("catalog.attr:{$id}", Config::get('cache.ttl', $this->cacheMinutes), function () use ($id) {
            return $this->getAttrRepository()->find($id);
        });
    }

    public function getSpec($id)
    {
        return Cache::remember("catalog.spec:{$id}", Config::get('cache.ttl', $this->cacheMinutes), function () use ($id) {
            return $this->getSpecRepository()->find($id);
        });
    }

    public function getSpecGroup($id)
    {
        return Cache::remember("catalog.spec_group:{$id}", Config::get('cache.ttl', $this->cacheMinutes), function () use ($id) {
            return $this->getSpecGroupRepository()->find($id);
        });
    }

    /**
     * 获取产品，查询所有与该产品相关的数据（公共数据不查询），缓存用
     *
     * @param $id
     * @return object
     * @throws \Throwable
     */
    public function getProduct($id)
    {
        $product = Cache::remember("catalog.product:{$id}", Config::get('cache.ttl', $this->cacheMinutes), function () use ($id) {
            $product = $this->getProductRepository()->find($id);
            throw_if(!$product || !$product->is_active, new NotFoundHttpException());
            $product->attrs = $this->getProductAttrRepository()->findAllBy('product_id', $id,
                ['attr_id', 'customer_value']);
            $product->specs = $this->getProductSpecRepository()
                ->findAllBy('product_id', $id, ['spec_id', 'customer_value', 'is_selected', 'picture_id', 'swatch_picture_id', 'sort'])
                ->map(function ($spec) {
                    $spec->picture = $this->imageService->getUrl($spec->picture_id);
                    $spec->swatch_picture = $this->imageService->getUrl($spec->swatch_picture_id);
                    return $spec;
                })
                ->sortBy('sort');
            $product->specGroups = $this->getProductSpecGroupRepository()->findAllBy('product_id', $id,
                ['spec_group_id', 'control_type', 'text_prompt', 'is_required', 'sort'])->sortBy('sort');
            $product->pictures = $this->getProductPictureRepository()
                ->findAllBy('product_id', $id)
                ->sortBy('sort')
                ->map(function ($media) { return $this->imageService->getUrl($media->picture_id); })
                ->values();
            $items = $this->getItemRepository()->findAllBy('product_id', $id);
            $itemSpecs = $this->getItemSpecRepository()->findAllBy('item_id', $items->pluck('id'))->groupBy('item_id');
            foreach ($items as $item) {
                $item->specs = $itemSpecs->get($item->id)->pluck('spec_id');
            }
            $product->items = $items;

            return $product;
        });

        $product->size_chart = ['data' => []];
        if (!empty($product->content) && ($json = json_decode($product->content, true))) {
            $product->size_chart = data_get($json, 'size_chart');
        }

        $product->specGroups = $product->specGroups->map(function ($prodSpecGroup) {
            $specGroup = $this->getSpecGroup($prodSpecGroup->spec_group_id);
            $prodSpecGroup->name = !empty($prodSpecGroup->text_prompt) ? $prodSpecGroup->text_prompt : $specGroup->name;
            $prodSpecGroup->id = $specGroup->id;
            $prodSpecGroup->slug = $specGroup->slug;
            $prodSpecGroup->description = $specGroup->description;
            return $prodSpecGroup;
        });

        $product->specs = $product->specs->map(function($prodSpec) {
            $spec = $this->getSpec($prodSpec->spec_id);
            $prodSpec->name = !empty($prodSpec->customer_value) ? $prodSpec->customer_value : $spec->name;
            $prodSpec->id = $spec->id;
            $prodSpec->group_id = $spec->group_id;
            $prodSpec->slug = $spec->slug;
            $prodSpec->description = $spec->description;
            return $prodSpec;
        });

        $attrGroups = collect([]);
        $product->attrs = $product->attrs->map(function($prodAttr) use ($attrGroups) {
            $attr = $this->getAttr($prodAttr->attr_id);
            if (!isset($attrGroups[$attr->group_id]))
                $attrGroups[$attr->group_id] = $this->getAttrGroup($attr->group_id);
            $prodAttr->name = !empty($prodAttr->customer_value) ? $prodAttr->customer_value : $attr->name;
            $prodAttr->id = $attr->id;
            $prodAttr->group_id = $attr->group_id;
            $prodAttr->slug = $attr->slug;
            $prodAttr->description = $attr->description;
            return $prodAttr;
        });
        $product->attrGroups = $attrGroups;

        $master = $product->items->first();
        foreach ($product->items as $item) {
            $item->amount = $this->currencyService->createBaseAmount($item->amount);
            if ($item->is_master) {
                $master = $item;
            }
        }
        if ($master) {
            foreach (['amount', 'discount', 'sku', 'quantity'] as $prop) {
                $product->{$prop} = $master->{$prop};
            }
        }

        return $product;
    }

    public function getCategory($id)
    {
        return Cache::remember("catalog.category:{$id}", Config::get('cache.ttl', $this->cacheMinutes), function () use ($id) {
            $category = $this->getCategoryRepository()->find($id);
            $category->config = $this->getConfigurationRepository()
                ->findAllBy(['configable_type' => 'catalog.category', 'configable_id' => $id], null, ['key', 'value'])
                ->pluck('value', 'key');
            return $category;
        });
    }

    public function getProductRepository()
    {
        if (!$this->products) {
            $this->products = new SimpleRepository(Config::get('catalog.products_table'));
        }

        return $this->products;
    }

    public function getCategoryRepository()
    {
        if (!$this->categories) {
            $this->categories = new SimpleRepository(Config::get('catalog.categories_table'));
        }

        return $this->categories;
    }

    public function getSpecRepository()
    {
        if (!$this->specs) {
            $this->specs = new SimpleRepository(Config::get('catalog.specs_table'));
        }

        return $this->specs;
    }

    public function getProductSpecRepository()
    {
        if (!$this->productSpecs) {
            $this->productSpecs = new SimpleRepository(Config::get('catalog.product_spec_table'));
        }

        return $this->productSpecs;
    }

    public function getProductSpecGroupRepository()
    {
        if (!$this->productSpecGroups) {
            $this->productSpecGroups = new SimpleRepository(Config::get('catalog.product_spec_group_table'));
        }

        return $this->productSpecGroups;
    }

    public function getSpecGroupRepository()
    {
        if (!$this->specGroups) {
            $this->specGroups = new SimpleRepository(Config::get('catalog.spec_groups_table'));
        }

        return $this->specGroups;
    }

    public function getProductAttrRepository()
    {
        if (!$this->productAttrs) {
            $this->productAttrs = new SimpleRepository(Config::get('catalog.product_attr_table'));
        }

        return $this->productAttrs;
    }

    public function getAttrRepository()
    {
        if (!$this->attrs) {
            $this->attrs = new SimpleRepository(Config::get('catalog.attrs_table'));
        }

        return $this->attrs;
    }

    public function getAttrGroupRepository()
    {
        if (!$this->attrGroups) {
            $this->attrGroups = new SimpleRepository(Config::get('catalog.attr_groups_table'));
        }

        return $this->attrGroups;
    }

    public function getItemRepository()
    {
        if (!$this->items) {
            $this->items = new SimpleRepository(Config::get('catalog.items_table'));
        }

        return $this->items;
    }

    public function getItemSpecRepository()
    {
        if (!$this->itemSpecs) {
            $this->itemSpecs = new SimpleRepository(Config::get('catalog.item_spec_table'));
        }

        return $this->itemSpecs;
    }

    public function getProductPictureRepository()
    {
        if (!$this->productPictures) {
            $this->productPictures = new SimpleRepository(Config::get('catalog.product_picture_table'));
        }

        return $this->productPictures;
    }

    public function getConfigurationRepository()
    {
        if (!$this->configurations) {
            $this->configurations = new SimpleRepository(Config::get('configuration.configables_table'));
        }

        return $this->configurations;
    }
}