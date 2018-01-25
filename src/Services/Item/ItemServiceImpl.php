<?php

namespace Viviniko\Catalog\Services\Item;

use Viviniko\Catalog\Contracts\ItemService;
use Viviniko\Catalog\Repositories\Item\ItemRepository;

class ItemServiceImpl implements ItemService
{
    /**
     * @var \Viviniko\Catalog\Repositories\Item\ItemRepository
     */
    protected $itemRepository;

    public function __construct(ItemRepository $itemRepository)
    {
        $this->itemRepository = $itemRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return $this->itemRepository->find($id);
    }
}