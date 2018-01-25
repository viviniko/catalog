<?php

namespace Viviniko\Catalog\Observers;

use Viviniko\Catalog\Models\Category;
use Viviniko\Catalog\Repositories\Category\CategoryRepository;

class CategoryObserver
{
    protected $categories;

    public function __construct(CategoryRepository $categories)
    {
        $this->categories = $categories;
    }

    public function saved(Category $category)
    {
        $path = $category->parent ? $category->parent->path : '';
        $path = trim($path . '/' . $category->id, '/');
        if ($category->path != $path) {
            $this->categories->update($category->id, ['path' => $path]);
        }
    }
}