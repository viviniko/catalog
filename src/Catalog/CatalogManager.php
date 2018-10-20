<?php

namespace Viviniko\Catalog\Catalog;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Viviniko\Catalog\Contracts\Catalog;
use Viviniko\Repository\SimpleRepository;

class CatalogManager implements Catalog
{
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

    protected $cacheSeconds = 180;

    public function getAttrGroup($id)
    {
        return Cache::remember("catalog.specGroup:{$id}", $this->cacheSeconds, function () use ($id) {
            return $this->getAttrGroupRepository()->find($id);
        });
    }

    public function getAttr($id)
    {
        return Cache::remember("catalog.specGroup:{$id}", $this->cacheSeconds, function () use ($id) {
            return $this->getAttrRepository()->find($id);
        });
    }

    public function getSpec($id)
    {
        return Cache::remember("catalog.specGroup:{$id}", $this->cacheSeconds, function () use ($id) {
            return $this->getSpecRepository()->find($id);
        });
    }

    public function getSpecGroup($id)
    {
        return Cache::remember("catalog.specGroup:{$id}", $this->cacheSeconds, function () use ($id) {
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
        $product = Cache::remember("catalog.product:{$id}", $this->cacheSeconds, function () use ($id) {
            $product = $this->getProductRepository()->find($id);
            throw_if(!$product || !$product->is_active, new NotFoundHttpException());
            $product->attrs = $this->getProductAttrRepository()->findAllBy('product_id', $id,
                ['attr_id', 'customer_value']);
            $product->specs = $this->getProductSpecRepository()->findAllBy('product_id', $id,
                ['spec_id', 'customer_value', 'is_selected', 'picture_id', 'swatch_picture_id', 'sort'])->sortBy('sort');
            $product->specGroups = $this->getProductSpecGroupRepository()->findAllBy('product_id', $id,
                ['spec_group_id', 'control_type', 'text_prompt', 'is_required', 'sort'])->sortBy('sort');
            $product->pictures = $this->getProductPictureRepository()->findAllBy('product_id', $id)->sortBy('sort');
            $items = $this->getItemRepository()->findAllBy('product_id', $id);
            $itemSpecs = $this->getItemSpecRepository()->findAllBy('item_id', $items->pluck('id'))->groupBy('item_id');
            foreach ($items as $item) {
                $item->specs = $itemSpecs->get($item->id)->pluck('spec_id');
            }
            $product->items = $items;

            return $product;
        });

        $product->specGroups = $product->specGroups->map(function ($prodSpecGroup) {
            $specGroup = $this->getSpecGroup($prodSpecGroup->spec_group_id);
            $prodSpecGroup->name = !empty($prodSpecGroup->text_prompt) ? $prodSpecGroup->text_prompt : $specGroup->name;
            return $prodSpecGroup;
        });

        $product->specs = $product->specs->map(function($prodSpec) {
            $spec = $this->getSpec($prodSpec->spec_id);
            $prodSpec->name = !empty($prodSpec->customer_value) ? $prodSpec->customer_value : $spec->name;
            return $prodSpec;
        });

        $attrGroups = collect([]);
        $product->attrs = $product->attrs->map(function($prodAttr) use ($attrGroups) {
            $attr = $this->getAttr($prodAttr->attr_id);
            if (!isset($attrGroups[$attr->group_id]))
                $attrGroups[$attr->group_id] = $this->getSpecGroup($attr->group_id);
            $prodAttr->name = !empty($prodAttr->customer_value) ? $prodAttr->customer_value : $attr->name;
            return $prodAttr;
        });

        $product->attrGroups = $attrGroups;

        return $product;
    }

    public function getCategory($id)
    {
        return $this->getCategoryRepository()->find($id);
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
}