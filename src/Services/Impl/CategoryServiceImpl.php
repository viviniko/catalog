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
    public function find($id)
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return collect($id)->map(function ($item) {
                return $this->find($item);
            });
        }

        return Cache::tags('catalog.categories')->remember("catalog.categories.category?:{$id}", Config::get('cache.ttl', 10), function () use ($id) {
            return $this->categoryRepository->find($id);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getChildrenId($id, $recursive = true)
    {
        return Cache::tags(['catalog.categories', 'catalog.categories.low'])->remember("catalog.categories.category_children_id?:{$id}:" . (int) $recursive, Config::get('cache.ttl', 10), function () use ($id, $recursive) {
            return $this->categoryRepository->getChildren($id, ['id'], $recursive)->pluck('id');
        });
    }

    /**
     * {@inheritdoc}
     */
    public function listen($event)
    {

    }
}