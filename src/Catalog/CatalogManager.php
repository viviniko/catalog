<?php

namespace Viviniko\Catalog\Catalog;

use Illuminate\Support\Facades\Config;
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

    public function getAttrGroupsByCategoryId($categoryId)
    {
        return $this->getAttrGroupRepository()->findAllBy('category_id', $categoryId);
    }

    public function getProductAttrsByProductId($productId)
    {
        $productAttrs = $this->getProductAttrRepository()->findAllBy('product_id', $productId);
        $attrs = $this->getAttrRepository()->findAllBy('id', $productAttrs->pluck('attr_id'));

        return $attrs;
    }

    public function getProductItemsByProductId($productId)
    {
        $items = $this->getItemRepository()->findAllBy('product_id', $productId);
        $itemSpecs = $this->getItemSpecRepository()
            ->findAllBy('item_id', $items->pluck('id'))
            ->groupBy('item_id');
        foreach ($items as $item) {
            $item->specs = $itemSpecs->get($item->id)->pluck('spec_id');
        }

        return $items;
    }

    public function getProductSpecGroupsByProductId($productId)
    {
        $productSpecGroups = $this->getProductSpecGroupRepository()
            ->findAllBy('product_id', $productId,
                ['spec_group_id', 'control_type', 'text_prompt', 'is_required', 'sort']);
        $specGroups = $this->getSpecGroupRepository()
            ->findAllBy('id', $productSpecGroups->pluck('spec_group_id'), ['id', 'name'])
            ->pluck('name', 'id');
        foreach ($productSpecGroups as $productSpecGroup) {
            $productSpecGroup->name = !empty($productSpecGroup->text_prompt)
                ? $productSpecGroup->text_prompt
                : ($specGroups[$productSpecGroup->spec_group_id] ?? '^_^');
        }

        return $productSpecGroups->sortBy('sort');
    }

    public function getProductSpecsByProductId($productId)
    {
        $productSpecs = $this->getProductSpecRepository()->findAllBy('product_id', $productId,
            ['spec_id', 'customer_value', 'is_selected', 'picture_id', 'swatch_picture_id', 'sort']);
        $specs = $this->getSpecRepository()
            ->findAllBy('id', $productSpecs->pluck('spec_id'), ['id', 'name'])
            ->pluck('name', 'id');
        foreach ($productSpecs as $productSpec) {
            $productSpec->name = !empty($productSpec->customer_value)
                ? $productSpec->customer_value
                : ($specs[$productSpec->spec_id] ?? '^_^');
        }

        return $productSpecs->sortBy('sort');
    }

    public function getProduct($id)
    {
        return $this->getProductRepository()->find($id);
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
}