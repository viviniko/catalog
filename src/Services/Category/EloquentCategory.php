<?php

namespace Viviniko\Catalog\Services\Category;

use Viviniko\Catalog\Contracts\CategoryService as CategoryServiceInterface;
use Viviniko\Catalog\Contracts\SpecificationGroupService;
use Viviniko\Catalog\Contracts\SpecificationService;
use Viviniko\Repository\SimpleRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EloquentCategory extends SimpleRepository implements CategoryServiceInterface
{
    protected $modelConfigKey = 'catalog.category';

    protected $fieldSearchable = [
        'categories' => 'category_id:in',
    ];

    /**
     * @var \Viviniko\Catalog\Contracts\SpecificationGroupService
     */
    protected $specificationGroupService;

    /**
     * @var \Viviniko\Catalog\Contracts\SpecificationService
     */
    protected $specificationService;

    /**
     * EloquentCategory constructor.
     * @param \Viviniko\Catalog\Contracts\SpecificationGroupService $specificationGroupService
     * @param \Viviniko\Catalog\Contracts\SpecificationService $specificationService
     */
    public function __construct(SpecificationGroupService $specificationGroupService, SpecificationService $specificationService)
    {
        $this->specificationGroupService = $specificationGroupService;
        $this->specificationService = $specificationService;
    }

    /**
     * Get all children.
     *
     * @param int $categoryId
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllChildren($categoryId)
    {
        $category = $this->find($categoryId);
        $children = collect([]);

        if ($category->children->isNotEmpty()) {
            foreach ($category->children as $child) {
                $children->push($child);
                $children = $children->merge($this->getAllChildren($child->id));
            }
        }

        return $children;
    }

    /**
     * Get category groups.
     *
     * @param $categoryId
     * @return mixed
     */
    public function getSpecificationGroups($categoryId)
    {
        $category = $this->find($categoryId);

        return $this->specificationGroupService->findByCategoryId(explode('/', $category->path));
    }

    /**
     * Get specification attributes by category id.
     *
     * @param $categoryId
     * @return mixed
     */
    public function getSpecifications($categoryId)
    {
        $categories = $this->getAllChildren($categoryId)->pluck('id')->prepend($categoryId)->toArray();
        $productTableName = Config::get('catalog.products_table');
        $productSpecificationTableName = Config::get('catalog.product_specification_table');

        return $this->specificationService->findIn(DB::table($productSpecificationTableName)->whereIn('product_id', function ($query) use ($categories, $productTableName) {
            $query->select('id')->from($productTableName)->whereIn('category_id', $categories);
        })->distinct()->pluck('specification_id'));
    }
}