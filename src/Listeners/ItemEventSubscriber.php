<?php

namespace Viviniko\Catalog\Listeners;

use Illuminate\Support\Facades\Cache;
use Viviniko\Catalog\Events\Item\ItemCreated;
use Viviniko\Catalog\Events\Item\ItemDeleted;
use Viviniko\Catalog\Events\Item\ItemUpdated;
use Viviniko\Support\Event\EventSubscriber;

class ItemEventSubscriber extends EventSubscriber
{
    protected $handlers = [
        ItemCreated::class => 'onItemCreated',
        ItemUpdated::class => 'onItemUpdated',
        ItemDeleted::class => 'onItemDeleted',
    ];

    public function onItemCreated($event)
    {
        Cache::tags('catalog.items.low')->flush();
    }

    public function onItemUpdated($event)
    {
        Cache::forget("catalog.product:{$event->item->product_id}");
    }

    public function onItemDeleted($event)
    {
        Cache::tags('catalog.items.low')->flush();
        Cache::forget("catalog.product:{$event->item->product_id}");
    }
}