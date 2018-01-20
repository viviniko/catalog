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
    public function getFilterableSpecificationsByCategoryId($categoryId)
    {
        return Cache::tags('catalog.specifications')->remember('catalog.specification.category-filterable?:' . $categoryId, Config::get('cache.ttl', 10), function () use ($categoryId) {
            $productTableName = Config::get('catalog.products_table');
            $productSpecificationTableName = Config::get('catalog.product_specification_table');
            $categories = $this->categoryRepository->getChildren($categoryId, ['id'], true)->pluck('id')->prepend($categoryId)->toArray();
            $specificationIds = DB::table($productSpecificationTableName)
                ->whereIn('product_id', function ($query) use ($categories, $productTableName) {
                    $query->select('id')->from($productTableName)->whereIn('category_id', $categories)->where('is_active', true);
                })->distinct()->pluck('specification_id');

            return $this->specificationRepository->getFilterableSpecifications($specificationIds);
        });
    }
}