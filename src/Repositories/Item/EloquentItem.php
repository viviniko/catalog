<?php

namespace Viviniko\Catalog\Repositories\Item;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\Config;
use Viviniko\Catalog\Events\Item\ItemCreated;
use Viviniko\Catalog\Events\Item\ItemDeleted;
use Viviniko\Catalog\Events\Item\ItemUpdated;
use Viviniko\Repository\EloquentRepository;

class EloquentItem extends EloquentRepository implements ItemRepository
{
    protected $searchRules = ['sku', 'is_master'];

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
        parent::__construct(Config::get('catalog.item'));
        $this->events = $events;
    }

    /**
     * {@inheritdoc}
     */
    public function findByProductId($productId)
    {
        return $this->findBy('product_id', $productId);
    }

    /**
     * {@inheritdoc}
     */
    public function findMasterByProductId($productId)
    {
        return $this->createQuery()->where(['product_id' => $productId, 'is_master' => true])->first();
    }

    protected function postCreate($item)
    {
        $this->events->dispatch(new ItemCreated($item));
    }

    protected function postUpdate($item)
    {
        $this->events->dispatch(new ItemUpdated($item));
    }

    protected function postDelete($item)
    {
        $this->events->dispatch(new ItemDeleted($item));
    }
}