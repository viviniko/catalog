<?php

namespace Viviniko\Catalog\Services\Item;

use Illuminate\Support\Facades\DB;
use Viviniko\Catalog\Contracts\ItemService;
use Viviniko\Catalog\Contracts\ProductSkuGenerater;
use Viviniko\Catalog\Repositories\Item\ItemRepository;

class ItemServiceImpl implements ItemService
{
    /**
     * @var \Viviniko\Catalog\Repositories\Item\ItemRepository
     */
    protected $itemRepository;

    /**
     * @var \Viviniko\Catalog\Contracts\ProductSkuGenerater
     */
    protected $productSkuGenerater;

    public function __construct(ItemRepository $itemRepository, ProductSkuGenerater $productSkuGenerater)
    {
        $this->itemRepository = $itemRepository;
        $this->productSkuGenerater = $productSkuGenerater;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return $this->itemRepository->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function createByAttributes($productId, array $attributes, array $data = [])
    {
        $item = null;
        DB::transaction(function () use ($productId, $attributes, $data, &$item) {
            $item = $this->itemRepository->create(array_merge([
                'product_id' => $productId,
                'sku' => '',
                'price' => 0,
                'weight' => 0,
                'quantity' => 0,
                'is_active' => true,
                'is_master' => false,
            ], $data));

            $item->attributes()->attach($attributes);
        });

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            if ($item = $this->itemRepository->find($id)) {
                $item->attributes()->sync([]);
            }

            return $this->itemRepository->delete($id);
        });

    }

    /**
     * {@inheritdoc}
     */
    public function deleteByProductId($productId)
    {
        return DB::transaction(function () use ($productId) {
            $items = $this->itemRepository->findByProductId($productId);
            foreach ($items as $item) {
                $this->delete($item->id);
            }

            return $items->count();
        });
    }
}