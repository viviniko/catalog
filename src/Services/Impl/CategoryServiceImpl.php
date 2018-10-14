<?php

namespace Viviniko\Catalog\Services\Impl;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Viviniko\Catalog\Services\CategoryService as CategoryServiceInterface;
use Viviniko\Catalog\Repositories\Category\CategoryRepository;

class CategoryServiceImpl implements CategoryServiceInterface
{
    /**
     * @var \Viviniko\Catalog\Repositories\Category\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * EloquentCategory constructor.
     * @param \Viviniko\Catalog\Repositories\Category\CategoryRepository
     */
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->categoryRepository->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getCategory($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {

        }

        return Cache::tags('catalog.categories')->remember("catalog.categories.category?:{$id}", Config::get('cache.ttl', 10), function () use ($id) {
            return $this->categoryRepository->getCategory($id);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryByIdIn(array $id)
    {
        return $this->categoryRepository->findAllBy('id', $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($categoryId, $recursive = true)
    {
        $children = collect([]);

        foreach ($this->categoryRepository->findAllBy('parent_id', $categoryId) as $category) {
            $children->push($category);
            if ($recursive) {
                $children = $children->merge($this->getChildren($category->id, $recursive));
            }
        }

        return $children;
    }
}