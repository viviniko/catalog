<?php

namespace Viviniko\Catalog\Repositories\Category;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Config;
use Viviniko\Catalog\Events\Category\CategoryCreated;
use Viviniko\Catalog\Events\Category\CategoryDeleted;
use Viviniko\Catalog\Events\Category\CategoryUpdated;
use Viviniko\Repository\EloquentRepository;

class EloquentCategory extends EloquentRepository implements CategoryRepository
{
    protected $searchRules = [
        'categories' => 'category_id:in',
    ];

    /**
     * @var \Illuminate\Contracts\Events\Dispatcher
     */
    protected $events;

    /**
     * EloquentCategory constructor.
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function __construct(Dispatcher $events)
    {
        parent::__construct(Config::get('catalog.category'));
        $this->events = $events;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren($categoryId, $columns = ['*'], $recursive = false)
    {
        $children = collect([]);

        foreach ($this->createQuery()->where('parent_id', $categoryId)->get($columns) as $category) {
            $children->push($category);
            if ($recursive) {
                $children = $children->merge($this->getChildren($category->id, $columns, $recursive));
            }
        }

        return $children;
    }

    protected function postCreate($category)
    {
        $this->events->dispatch(new CategoryCreated($category));
    }

    protected function postUpdate($category)
    {
        $this->events->dispatch(new CategoryUpdated($category));
    }

    protected function postDelete($category)
    {
        $this->events->dispatch(new CategoryDeleted($category));
    }
}