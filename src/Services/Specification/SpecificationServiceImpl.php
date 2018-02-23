<?php

namespace Viviniko\Catalog\Services\Specification;

use Viviniko\Catalog\Contracts\SpecificationService as SpecificationServiceInterface;
use Viviniko\Catalog\Repositories\Category\CategoryRepository;
use Viviniko\Catalog\Repositories\Specification\SpecificationRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SpecificationServiceImpl implements SpecificationServiceInterface
{
    protected $specificationRepository;

    protected $categoryRepository;

    public function __construct(SpecificationRepository $specificationRepository, CategoryRepository $categoryRepository)
    {
        $this->specificationRepository = $specificationRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return $this->specificationRepository->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterableSpecificationsByCategoryId($categoryId)
    {
        return Cache::tags('catalog.specifications')->remember('catalog.specification.category-filterable?:' . $categoryId, Config::get('cache.ttl', 10), function () use ($categoryId) {
            return $this->specificationRepository->getFilterableSpecificationsByCategoryId($this->categoryRepository->getChildren($categoryId, ['id'], true)->pluck('id')->prepend($categoryId)->toArray());
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchableSpecificationsByProductId($productId)
    {
       return $this->specificationRepository->getSearchableSpecificationsByProductId($productId);
    }

    /**
     * {@inheritdoc}
     */
    public function getViewableSpecificationsByProductId($productId)
    {
        return $this->specificationRepository->getViewableSpecificationsByProductId($productId);
    }
}