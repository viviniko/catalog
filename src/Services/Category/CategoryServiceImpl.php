<?php

namespace Viviniko\Catalog\Services\Category;

use Viviniko\Catalog\Contracts\CategoryService as CategoryServiceInterface;
use Viviniko\Catalog\Repositories\Category\CategoryRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class CategoryServiceImpl implements CategoryServiceInterface
{
    protected $categoryRepository;

    /**
     * EloquentCategory constructor.
     * @param \Common\Catalog\Repositories\Category\CategoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
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

        return Cache::tags('catalog.categories')->remember("catalog.category?:{$id}", Config::get('cache.ttl', 10), function () use ($id) {
            return $this->categoryRepository->find($id);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenId($id, $recursive = true)
    {
        return Cache::tags('catalog.categories')->remember($recursive ? "catalog.category.all-children-id?:{$id}" :"catalog.category.children-id?:{$id}", Config::get('cache.ttl', 10), function () use ($id, $recursive) {
            return $this->categoryRepository->getChildren($id, ['id'], $recursive)->pluck('id');
        });
    }
}