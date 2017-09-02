<?php

namespace Viviniko\Catalog\Observers;

use Viviniko\Catalog\Contracts\CategoryService;
use Viviniko\Catalog\Models\Category;

class CategoryObserver
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function saved(Category $category)
    {
        $path = $category->parent ? $category->parent->path : '';
        $path = trim($path . '/' . $category->id, '/');
        if ($category->path != $path) {
            $this->categoryService->update($category->id, ['path' => $path]);
        }
    }
}