<?php

namespace Viviniko\Catalog\Repositories\Product;

use Viviniko\Media\Contracts\ImageService;
use Viviniko\Repository\SimpleRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EloquentProduct extends SimpleRepository implements ProductRepository
{
    protected $modelConfigKey = 'catalog.product';

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage = null, $searchName = 'search', $search = null)
    {
        $productTable = Config::get('catalog.products_table');
        $productManufacturerTable = Config::get('catalog.product_manufacturer_table');
        $productItemsTable = Config::get('catalog.product_items_table');
        $manufacturerTable = Config::get('catalog.manufacturers_table');
        $categoryTable = Config::get('catalog.categories_table');
        $taggablesTable = Config::get('tag.taggables_table');

        $this->fieldSearchable = [
            'id' => "{$productTable}.id:=",
            'name' => "{$productTable}.name:like",
            'category' => "{$categoryTable}.id:=",
            'sku' => 'like',
            'manufacturer' => "{$manufacturerTable}.id:=",
            'product_origin_sku' => "{$productManufacturerTable}.product_origin_sku:like",
            'manufacturer_status' => "{$productManufacturerTable}.status:=",
            'price' => "{$productItemsTable}.price:=",
            'market_price' => "{$productItemsTable}.market_price:=",
            'stock_quantity' => "{$productItemsTable}.stock_quantity:=",
            'created_at' => "{$productTable}.created_at:betweenDate",
            'updated_at' => "{$productTable}.updated_at:betweenDate",
            'created_by' => 'like',
            'updated_by' => 'like',
            'is_active' => "{$productTable}.is_active:=",
        ];

        $builder = parent::search($search)->select(["{$productTable}.*"])
            ->join($categoryTable, "{$productTable}.category_id", '=', "{$categoryTable}.id", 'left')
            ->join($productManufacturerTable, "{$productTable}.id", '=', "{$productManufacturerTable}.product_id", 'left')
            ->join($manufacturerTable, "{$manufacturerTable}.id", '=', "{$productManufacturerTable}.manufacturer_id", 'left')
            ->join($productItemsTable, "{$productTable}.id", '=', "{$productItemsTable}.product_id", 'left')
            ->where("{$productItemsTable}.is_master", true);
        if (isset($search['has_tag'])) {
            if ($search['has_tag'] == '1') {
                $builder->has('tags');
            } else {
                $builder->doesntHave('tags');
            }
        }
        if (isset($search['tags'])) {
            $builder->join("$taggablesTable", "{$productTable}.id", '=', "{$taggablesTable}.taggable_id", 'left');
            $builder->whereIn("{$taggablesTable}.tag_id", $search['tags']);
        }

        $builder->orderBy('created_at', 'desc');

        $result = $builder->paginate($perPage);

        if (!empty($search)) {
            $query = [];
            foreach($search as $key => $value) {
                $query[$searchName][$key] = $value;
            }

            $result->appends($query);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data)
    {
        $product = null;

        DB::transaction(function () use ($data, &$product) {
            $product = parent::create($data);

            if (!empty($data['specifications'])) {
                $product->specifications()->sync($data['specifications']);
            }
            $product->tags()->sync($data['tags']);

            $data['sku'] = isset($data['sku']) ? (string)$data['sku'] : '';
            $data['is_master'] = true;
            $data['upc'] = isset($data['upc']) ? (string)$data['upc'] : '';
            $data['market_price'] = isset($data['market_price']) ? (float)$data['market_price'] : 0;
            $data['stock_quantity'] = isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : 0;
            $product->items()->updateOrCreate(['product_id' => $product->id, 'is_master' => true], $data);

            if (!empty($data['manufacturer_id'])) {
                $product->manufacturer()->updateOrCreate([
                    'product_id' => $product->id,
                    'manufacturer_id' => $data['manufacturer_id'],
                ], $data);
            }

            if (!empty($data['pictures'])) {
                $product->pictures()->sync($data['pictures']);
                foreach ($data['pictures'] as $i => $picture) {
                    $product->pictures()->updateExistingPivot($picture, ['sort' => $i]);
                }
            }
        });

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function update($id, array $data)
    {
        $product = null;

        DB::transaction(function () use ($id, $data, &$product) {
            $product = parent::update($id, $data);

            if (isset($data['is_active'])) {
                unset($data['is_active']);
            }

            if (!empty($data['specifications'])) {
                $product->specifications()->sync($data['specifications']);
            }
            $product->tags()->sync($data['tags']);

            $data['sku'] = isset($data['sku']) ? (string)$data['sku'] : '';
            $data['stock_quantity'] = isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : 0;
            $data['upc'] = isset($data['upc']) ? (string)$data['upc'] : '';
            $data['market_price'] = isset($data['market_price']) ? (float)$data['market_price'] : 0;
            $product->items()->updateOrCreate(['product_id' => $product->id, 'is_master' => true], $data);

            if (!empty($data['manufacturer_id'])) {
                $product->manufacturer()->updateOrCreate([
                    'product_id' => $product->id,
                ], $data);
            }

            if (!empty($data['pictures'])) {
                $product->pictures()->sync($data['pictures']);
                foreach ($data['pictures'] as $i => $picture) {
                    $product->pictures()->updateExistingPivot($picture, ['sort' => $i]);
                }
            }
            // 当商品修改后，同步修改商品模块的数值
            $sku = $data['sku'];
            $widgetItems = app(\Common\Portal\Contracts\WidgetItemService::class)->createModel()->newQuery()
                ->whereRaw("extra-> '$.sku' = '$sku'")->get();
            $widgetItems->each(function ($item) use ($data) {
                $picture = isset($data['pictures']) && isset($data['pictures'][0]) ? app(ImageService::class)->find($data['pictures'][0]) : null;
                $item->update([
                    "title" => $data['name'],
                    "description" => $data['price'],
                    "url" => $data['url_rewrite'],
                    "image" => $picture ? $picture->url : null,
                    "image_alt" => $data['name'],
                ]);
            });
        });

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function createProductItem($productId, array $attributes, $index=1)
    {
        $productItem = null;
        DB::transaction(function () use ($productId, $attributes, &$productItem, $index) {
            $product = $this->find($productId);
            $productItem = $product->items()->create([
                'sku' => preg_replace('|[0-9/]+|','',$product->sku) . sprintf("%04d",$index),
                'upc' => '',
                'price' => 0,
                'weight' => $product->weight,
                'stock_quantity' => $product->stock_quantity,
                'is_active' => true,
                'is_master' => false,
            ]);

            $productItem->attributes()->attach(array_map(function ($item) {
                return $item instanceof Attribute ? $item->id : $item;
            }, $attributes));
        });

        return $productItem;
    }

    /**
     * {@inheritdoc}
     */
    public function changeProductStatus($productId, $status)
    {
        return DB::table($this->createModel()->getTable())->where('id', $productId)->update(['is_active' => $status ? 1 : 0]);
    }

    /**
     * {@inheritdoc}
     */
    public function getLatestProducts($take, $columns = ['*'])
    {
        return $this->createModel()->latest()->limit($take)->get($columns);
    }
}