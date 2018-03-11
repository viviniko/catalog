<?php

namespace Viviniko\Catalog\Services\Item;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
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
        if (is_array($id) || $id instanceof Arrayable) {
            return collect($id)->map(function ($item) {
                return $this->find($item);
            });
        }

        return Cache::tags('catalog.items')->remember("catalog.items.item?:{$id}", Config::get('cache.ttl', 10), function () use ($id) {
            return $this->itemRepository->find($id);
        });
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
                'picture_id' => $this->getPictureIdByProductAttributes($productId, $attributes),
                'sku' => '',
                'price' => 0,
                'weight' => 0,
                'quantity' => 0,
                'is_active' => true,
                'is_master' => false,
            ], $data));

            $item->attrs()->attach($attributes);
        });

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $item = $this->itemRepository->find($id);
            if (isset($data['is_master']) && $item->is_master != $data['is_master']) {
                $master = $this->itemRepository->findMasterByProductId($item->product_id);
                if (!$master) {
                    $data['is_master'] = true;
                } else if ($master->id != $item->id) {
                    if ($data['is_master']) {
                        $this->itemRepository->update($master->id, ['is_master' => false]);
                    }
                }
            }
            if (!isset($data['picture_id'])) {
                $data['picture_id'] = $this->getPictureIdByProductAttributes($item->product_id, $item->attrs->pluck('id'));
            }

            return $this->itemRepository->update($item->id, $data);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            if ($item = $this->itemRepository->find($id)) {
                $item->attrs()->sync([]);
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

    /**
     * Get item.
     *
     * @param $productId
     * @param array $attributes
     * @return mixed
     */
    public function findByProductAttributes($productId, array $attributes)
    {
        $productItemAttributeTable = Config::get('catalog.item_attribute_table');
        $productItemTable = Config::get('catalog.items_table');
        $productItemId = DB::table($productItemTable)
            ->select("$productItemTable.id")
            ->where('product_id', $productId)
            ->join($productItemAttributeTable, "$productItemTable.id", '=', "$productItemAttributeTable.item_id")
            ->whereIn("$productItemAttributeTable.attribute_id", $attributes)
            ->groupBy("$productItemTable.id")
            ->havingRaw("count($productItemTable.id)=" . count($attributes))
            ->first();
        if (!$productItemId) {
            $productItemId = DB::table($productItemTable)->select('id')->where(['product_id' => $productId, 'is_master' => '1'])->first();
        }

        return $productItemId ? $this->find($productItemId->id) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getPictureIdByProductAttributes($productId, $attributes)
    {
        $result = DB::table(Config::get('catalog.product_attribute_table'))
            ->where('product_id', $productId)
            ->whereIn('attribute_id', $attributes)
            ->whereNotNull('picture_id')
            ->distinct()
            ->first(['picture_id']);
        if (!$result) {
            $result = DB::table(Config::get('catalog.product_picture_table'))
                ->where('product_id', $productId)
                ->orderBy('sort')
                ->first(['picture_id']);
        }

        return $result ? $result->picture_id : 0;
    }
}