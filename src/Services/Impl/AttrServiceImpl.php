<?php

namespace Viviniko\Catalog\Services\Impl;

use Viviniko\Catalog\Repositories\Category\CategoryRepository;
use Viviniko\Catalog\Services\AttrService as AttrServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Viviniko\Catalog\Repositories\Attr\AttrRepository;

class AttrServiceImpl implements AttrServiceInterface
{
    protected $attributeRepository;

    protected $categoryRepository;

    public function __construct(AttrRepository $attributeRepository, CategoryRepository $categoryRepository)
    {
        $this->attributeRepository = $attributeRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return $this->attributeRepository->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterableAttrsByCategoryId($categoryId)
    {
        return Cache::tags('catalog.attrs')->remember('catalog.attr.category-filterable?:' . $categoryId, Config::get('cache.ttl', 10), function () use ($categoryId) {
            return $this->attributeRepository->getFilterableAttrsByCategoryId($this->categoryRepository->getChildren($categoryId, ['id'], true)->pluck('id')->prepend($categoryId)->toArray());
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableAttrsByProductId($productId)
    {
        return $this->attributeRepository->getSearchableAttrsByProductId($productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getViewableAttrsByProductId($productId)
    {
        return $this->attributeRepository->getViewableAttrsByProductId($productId);
    }
}