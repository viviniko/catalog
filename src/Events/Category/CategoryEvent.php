<?php

namespace Viviniko\Catalog\Events\Category;

use Viviniko\Catalog\Models\Category;

class CategoryEvent
{
    public $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }
}