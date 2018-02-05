<?php

namespace Viviniko\Catalog\Listeners;

use Illuminate\Support\Facades\Cache;
use Viviniko\Catalog\Contracts\CategoryService;
use Viviniko\Catalog\Events\Category\CategoryCreated;
use Viviniko\Catalog\Events\Category\CategoryDeleted;
use Viviniko\Catalog\Events\Category\CategoryUpdated;
use Viviniko\Support\Event\EventSubscriber;

class CategoryEventSubscriber extends EventSubscriber
{
    protected $handlers = [
        CategoryCreated::class => 'onCategoryCreated',
        CategoryUpdated::class => 'onCategoryUpdated',
        CategoryDeleted::class => 'onCategoryDeleted',
    ];

    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function onCategoryCreated($event)
    {
        Cache::tags('catalog.categories.low')->flush();
    }

    public function onCategoryUpdated($event)
    {
        Cache::tags('catalog.categories.low')->flush();
        Cache::forget('catalog.categories.category?:'. $event->category->id);
    }

    public function onCategoryDeleted($event)
    {
        Cache::tags('catalog.categories.low')->flush();
        Cache::forget('catalog.categories.category?:'. $event->category->id);
    }
}