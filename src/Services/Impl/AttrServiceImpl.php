<?php

namespace Viviniko\Catalog\Services\Impl;

use Viviniko\Catalog\Repositories\AttrGroup\AttrGroupRepository;
use Viviniko\Catalog\Services\AttrService as AttrServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Viviniko\Catalog\Repositories\Attr\AttrRepository;
use Viviniko\Catalog\Services\CategoryService;

class AttrServiceImpl implements AttrServiceInterface
{
    protected $attributeRepository;

    protected $attrGroupRepository;

    protected $categoryService;

    public function __construct(
        AttrRepository $attributeRepository,
        AttrGroupRepository $attrGroupRepository,
        CategoryService $categoryService)
    {
        $this->attributeRepository = $attributeRepository;
        $this->attrGroupRepository = $attrGroupRepository;
        $this->categoryService = $categoryService;
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
            return $this->attributeRepository->getFilterableAttrsByCategoryId($this->categoryService->getCategoryChildren($categoryId, ['id'], true)->pluck('id')->prepend($categoryId)->toArray());
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

    /**
     * {@inheritdoc}
     */
    public function getGroupsInCategoryId($categoryId)
    {
        return $this->attrGroupRepository->findAllBy('category_id', $categoryId);
    }

    /**
     * {@inheritdoc}
     */
    public function createGroup(array $data)
    {
        return $this->attrGroupRepository->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroup($attrGroupId, array $data)
    {
        return $this->attrGroupRepository->update($attrGroupId, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteGroup($attrGroupId)
    {
        return $this->attrGroupRepository->delete($attrGroupId);
    }

    /**
     * {@inheritdoc}
     */
    public function createAttr(array $data)
    {
        return $this->attributeRepository->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAttr($attrId, array $data)
    {
        return $this->attributeRepository->update($attrId, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAttr($attrId)
    {
        return $this->attributeRepository->delete($attrId);
    }
}