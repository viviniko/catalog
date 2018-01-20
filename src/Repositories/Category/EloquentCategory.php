<?php

namespace Viviniko\Catalog\Repositories\Category;

use Viviniko\Repository\SimpleRepository;

class EloquentCategory extends SimpleRepository implements CategoryRepository
{
    protected $modelConfigKey = 'catalog.category';

    protected $fieldSearchable = [
        'categories' => 'category_id:in',
    ];

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return $this->search([])->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($categoryId, $columns = ['*'], $recursive = false)
    {
        $children = collect([]);

        foreach ($this->createModel()->where('parent_id', $categoryId)->get($columns) as $category) {
            $children->push($category);
            if ($recursive) {
                $children = $children->merge($this->getChildren($category->id, $columns, $recursive));
            }
        }

        return $children;
    }
}