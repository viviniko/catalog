<?php

namespace Viviniko\Catalog\Services\Product;

use Carbon\Carbon;
use Viviniko\Catalog\Contracts\AttributeService;
use Viviniko\Catalog\Contracts\ProductService as ProductServiceInterface;
use Viviniko\Catalog\Contracts\ProductService;
use Viviniko\Catalog\Models\Attribute;
use Viviniko\Catalog\Repositories\Category\CategoryRepository;
use Viviniko\Catalog\Repositories\Specification\SpecificationRepository;
use Viviniko\Media\Contracts\ImageService;
use Viviniko\Repository\SimpleRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EloquentProduct extends SimpleRepository implements ProductServiceInterface
{
    protected $modelConfigKey = 'catalog.product';

    /**
     * @var \Viviniko\Catalog\Repositories\Category\CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @var \Viviniko\Catalog\Contracts\AttributeService
     */
    protected $attributeService;

    /**
     * @var \Viviniko\Catalog\Repositories\Specification\SpecificationRepository
     */
    protected $specificationRepository;

    /**
     * @var \Viviniko\Media\Contracts\ImageService
     */
    protected $imageService;

    /**
     * EloquentProduct constructor.
     * @param \Viviniko\Catalog\Repositories\Category\CategoryRepository
     * @param \Viviniko\Catalog\Contracts\AttributeService $attributeService
     * @param \Viviniko\Catalog\Repositories\Specification\SpecificationRepository
     * @param \Viviniko\Media\Contracts\ImageService $imageService
     */
    public function __construct(
        CategoryRepository $categoryRepository,
        AttributeService $attributeService,
        SpecificationRepository $specificationRepository,
        ImageService $imageService)
    {
        $this->categoryRepository = $categoryRepository;
        $this->attributeService = $attributeService;
        $this->specificationRepository = $specificationRepository;
        $this->imageService = $imageService;
    }

    /**
     * {@inheritdoc}
     */
    public function search($keyword)
    {
        return $this->createModel()->search($keyword);
    }

    /**
     * {@inheritdoc}
     */
    public function paginate($perPage = null, $search = null)
    {
        $productTable = Config::get('catalog.products_table');
        $productManufacturerTable = Config::get('catalog.manufacturer_products_table');
        $productItemsTable = Config::get('catalog.product_items_table');
        $manufacturerTable = Config::get('catalog.manufacturers_table');
        $categoryTable = Config::get('catalog.categories_table');
        $taggablesTable =Config::get('tag.taggables_table');


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
                $query['search'][$key] = $value;
            }

            $result->appends($query);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return collect($id)->map(function ($item) {
                return $this->find($item);
            });
        }

        return Cache::tags('catalog.products')->remember("catalog.product?:{$id}", Config::get('cache.ttl', 10), function () use ($id, $columns) {
            return parent::find($id, $columns);
        });
    }

    /**
     * Save a new entity in repository
     *
     * @param array $data
     *
     * @return mixed
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
     * Update a entity in repository by id
     *
     * @param       $id
     * @param array $data
     *
     * @return mixed
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

    public function changeProductStatus($productId, $status)
    {
        $product = $this->find($productId);

        if ($product) {
            $product->update(['is_active' => $status ? 1 : 0]);
        }

        return $product;
    }

    /**
     * Get product item.
     *
     * @param $productId
     * @param $productItemId
     * @return mixed
     */
    public function findProductItem($productId, $productItemId)
    {
        $product = $this->find($productId);

        return $product ? $product->items()->find($productItemId) : null;
    }

    /**
     * Create product item.
     *
     * @param $productId
     * @param array $attributes
     *
     * @return mixed
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
     * Update product item.
     *
     * @param $productId
     * @param $productItemId
     * @param array $data
     *
     * @return mixed
     */
    public function updateProductItem($productId, $productItemId, array $data)
    {
        $product = $this->find($productId);
        $productItem = $product->items()->find($productItemId);
        
        if(isset($data['upc'])){
            $data['upc'] = (string) data_get($data, 'upc');
        }
        
        if(isset($data['sku'])){
            $data['sku'] = (string) data_get($data, 'sku');
            $this->validateSku($data['sku'], $productItem->id);
        }
        $product->items()->save($productItem->fill($data));

        return $productItem;
    }

    /**
     * Delete product item.
     *
     * @param $productId
     * @param $productItemId
     *
     * @return mixed
     */
    public function deleteProductItem($productId, $productItemId)
    {
        $product = $this->find($productId);
        $productItem = $product->items()->find($productItemId);
        DB::transaction(function () use ($productItem) {
            $productItem->attributes()->sync([]);
            $productItem->delete();
        });

        return $productItem;
    }

    /**
     * Delete a entity in repository by id
     *
     * @param $id
     *
     * @return int
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {
            $product = $this->find($id);
            if ($product) {
                $product->items->each(function ($item) use ($id) {
                    $this->deleteProductItem($id, $item->id);
                });
                $product->pictures()->sync([]);
                $product->specifications()->sync([]);
                $product->attributeGroups()->sync([]);
                $product->attributes()->sync([]);
                DB::table(Config::get('catalog.product_manufacturer_table'))->where('product_id', $id)->delete();
                DB::table(Config::get('catalog.product_difference_table'))->where('product_id', $id)->delete();

                return parent::delete($id);
            }
        });
    }

    /**
     * Attach attribute groups.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function attachAttributeGroup($productId, array $data)
    {
        $product = $this->find($productId);
        foreach ($data as $groupId => $attributes) {
            $product->attributeGroups()->attach($groupId, $attributes);
        }

        return $product;
    }

    /**
     * Sync attribute groups.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function syncAttributeGroup($productId, array $data)
    {
        $product = $this->find($productId);
        $product->attributeGroups()->sync($data);

        return $product;
    }

    /**
     * Detach attribute groups.
     *
     * @param $productId
     * @param $groupId
     *
     * @return mixed
     */
    public function detachAttributeGroup($productId, $groupId)
    {
        $product = $this->find($productId);
        $product->attributeGroups()->detach($groupId);

        return $product;
    }

    /**
     * Update attribute group.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function updateAttributeGroup($productId, array $data)
    {
        $product = $this->find($productId);

        foreach ($data as $groupId => $attributes) {
            $product->attributeGroups()->updateExistingPivot($groupId, $attributes);
        }

        return $product;
    }

    /**
     * Attach attributes.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function attachAttribute($productId, array $data)
    {
        $product = $this->find($productId);
        foreach ($data as $attributeId => $attributes) {
            DB::transaction(function () use ($product, $attributes, $productId, $attributeId) {
                if (isset($attributes['is_selected']) && $attributes['is_selected']) {
                    $this->resetProductSelectedAttribute($productId, $attributeId);
                }
                $this->addProductAttributeSwatchPicture($attributes);
                $product->attributes()->attach($attributeId, $attributes);
            });
        }

        return $product;
    }

    /**
     * Sync attributes.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function syncAttribute($productId, array $data)
    {
        $product = $this->find($productId);
        $product->attributes()->sync($data);

        return $product;
    }

    /**
     * Detach attributes.
     *
     * @param $productId
     * @param $attributeId
     *
     * @return mixed
     */
    public function detachAttribute($productId, $attributeId)
    {
        $product = $this->find($productId);
        $product->attributes()->detach($attributeId);

        return $product;
    }

    /**
     * Update attributes.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function updateAttribute($productId, array $data)
    {
        $product = $this->find($productId);
        foreach ($data as $attributeId => $attributes) {
            DB::transaction(function () use ($product, $attributes, $productId, $attributeId) {
                if (isset($attributes['is_selected']) && $attributes['is_selected']) {
                    $this->resetProductSelectedAttribute($productId, $attributeId);
                }
                $this->addProductAttributeSwatchPicture($attributes);
                $product->attributes()->updateExistingPivot($attributeId, $attributes);
            });
        }

        return $product;
    }

    public function updateProductAttributeSwatchPicture($productId, $attributeId, $pictureId, $x, $y)
    {
        $product = $this->find($productId);
        if ($product) {
            $attributes = ['picture_id' => $pictureId];
            $this->addProductAttributeSwatchPicture($attributes, $x, $y);

            return $product->attributes()->updateExistingPivot($attributeId, $attributes);
        }

        return false;
    }

    /**
     * Detach picture.
     *
     * @param $pictureId
     *
     * @return mixed
     */
    public function detachPicture($pictureId)
    {
        DB::table(Config::get('catalog.product_picture_table'))->where('picture_id', $pictureId)->delete();
    }

    /**
     * Get attribute groups by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getAttributeGroups($productId)
    {
        return Cache::tags('catalog.products')->remember("catalog.product.attributeGroups?:{$productId}", Config::get('cache.ttl', 10), function () use ($productId) {
            $product = $this->find($productId);
            return $product ? $product->attributeGroups : collect([]);
        });
    }

    /**
     * Get attributes by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getAttributes($productId)
    {
        return Cache::tags('catalog.products')->remember("catalog.product.attributes?:{$productId}", Config::get('cache.ttl', 10), function () use ($productId) {
            $product = $this->find($productId);
            return $product ? $product->attributes : collect([]);
        });
    }

    /**
     * Get specifications by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getSpecifications($productId)
    {
        return Cache::tags('catalog.products')->remember("catalog.product.specifications?:{$productId}", Config::get('cache.ttl', 10), function () use ($productId) {
            $ids = DB::table(Config::get('catalog.product_specification_table'))
                ->where('product_id', $productId)
                ->pluck('specification_id');
            return $this->specificationRepository->findInWithGroup($ids);
        });

    }

    /**
     * Get product item.
     *
     * @param $productId
     * @param array $attributes
     * @return mixed
     */
    public function getProductItem($productId, array $attributes)
    {
        $productItemAttributeTable = Config::get('catalog.product_item_attribute_table');
        $productItemTable = Config::get('catalog.product_items_table');
        $productItem = DB::table($productItemTable)
            ->select("$productItemTable.id")
            ->where('product_id', $productId)
            ->join($productItemAttributeTable, "$productItemTable.id", '=', "$productItemAttributeTable.product_item_id")
            ->whereIn("$productItemAttributeTable.attribute_id", $attributes)
            ->groupBy("$productItemTable.id")
            ->havingRaw("count($productItemTable.id)=" . count($attributes))
            ->first();
        if (!$productItem) {
            $productItem = DB::table($productItemTable)->select('id')->where(['product_id' => $productId, 'is_master' => '1'])->first();
        }

        return $productItem ? $this->findProductItem($productId, $productItem->id) : null;
    }

    public function addProductAttributeSwatchPicture(array &$attributes, $x = null, $y = null)
    {
        $size = config('catalog.settings.swatch_picture_size', 60);
        if (isset($attributes['picture_id']) && $attributes['picture_id'] != 0 && !isset($attributes['swatch_picture_id'])) {
            $picture = $this->imageService->crop($attributes['picture_id'], $size, $size, $x, $y);

            $attributes['swatch_picture_id'] = $picture->id;
        }
    }

    public function resetProductSelectedAttribute($productId, $attributeId)
    {
        $attribute = $this->attributeService->find($attributeId);
        if ($attribute) {
            DB::table(Config::get('catalog.product_attribute_table'))->where('product_id', $productId)->whereIn('attribute_id', function ($query) use ($attribute) {
                $query->select('id')->from(Config::get('catalog.attributes_table'))->where('group_id', $attribute->group_id);
            })->update(['is_selected' => 0]);
        }
    }

    /**
     * Get product picture.
     *
     * @param $productId
     * @param $attributes
     * @return mixed
     */
    public function getProductPicture($productId, array $attributes)
    {
        if (!empty($attributes)) {
            $pictureIds = DB::table(Config::get('catalog.product_attribute_table'))
                ->where('product_id', $productId)
                ->whereIn('attribute_id', $attributes)
                ->whereNotNull('picture_id')
                ->distinct()
                ->pluck('picture_id');
            if ($pictureIds->isNotEmpty() && ($image = $this->imageService->getUrl($pictureIds->first()))) {
                return $image;
            }
        }

        return data_get($this->find($productId), 'cover.first');
    }

    public function getProductSwatchPictures($productId)
    {
        return DB::table(Config::get('catalog.product_attribute_table'))
            ->where('product_id', $productId)
            ->whereNotNull('swatch_picture_id')
            ->orderBy('sort')
            ->get()
            ->map(function ($item) {
                $swatch = new \stdClass();
                $swatch->swatch_picture_url = $this->imageService->getUrl($item->swatch_picture_id);
                $swatch->picture_url = $this->imageService->getUrl($item->picture_id);
                $swatch->attribute_id = $item->attribute_id;
                $swatch->swatch_picture_name = data_get($this->attributeService->find($item->attribute_id), 'title');
                return $swatch;
            });
    }

    /**
     * Get product sku.
     *
     * @param $productId
     * @param $attributes
     * @return mixed
     */
    public function getProductSku($productId, $attributes)
    {
        if ($this->checkProductAttributes($productId, $attributes)) {
            $productItem = $this->getProductItemByAttributes($productId, $attributes);

            return $productItem ? $productItem->sku : $this->find($productId)->sku;
        }

        return null;
    }

    /**
     * Get product sku id.
     *
     * @param $productId
     * @param $attributes
     * @return int|string
     */
    public function getProductSkuId($productId, $attributes)
    {
        // if ($productItem = $this->getProductItemByAttributes($productId, $attributes)) {
        //     return $productItem->id;
        // }

        if (!$this->checkProductAttributes($productId, $attributes)) {
            return false;
        }
        $skuId[] = 'P' . $productId;
        foreach ($this->attributeService->findIn($attributes)->sortBy('group.name') as $attribute) {
            $skuId[] = $attribute->group->name[0] . $attribute->id;
        }

        return strtoupper(implode('', $skuId));
    }

    public function getProductItemByAttributes($productId, array $attributes)
    {
        $productItemAttributeTable = Config::get('catalog.product_item_attribute_table');
        $productItemTable = Config::get('catalog.product_items_table');
        if (!$this->checkProductAttributes($productId, $attributes)) {
            return false;
        }
        $query = DB::table($productItemTable)->select("$productItemTable.*")->where('product_id', $productId);
        if (!empty($attributes)) {
            $query->join($productItemAttributeTable, "$productItemTable.id", '=', "$productItemAttributeTable.product_item_id")
                ->whereIn("$productItemAttributeTable.attribute_id", $attributes)
                ->groupBy("$productItemTable.id")
                ->havingRaw("count($productItemTable.id)=" . count($attributes));

        }

        return $query->first();
    }

    public function checkProductAttributes($productId, array $attributes)
    {
        $requiredAttributeGroupIds = DB::table(Config::get('catalog.product_attribute_group_table'))->where(['product_id' => $productId, 'is_required' => 1])->pluck('attribute_group_id');
        if (empty($requiredAttributeGroupIds)) {
            return true;
        }
        if (empty($attributes)) {
            return false;
        }
        $attributeGroupIds = DB::table(Config::get('catalog.attributes_table'))->whereIn('id', $attributes)->pluck('group_id');

        return $requiredAttributeGroupIds->diff($attributeGroupIds)->isEmpty();
    }

    /**
     * Count manufacturer product.
     *
     * @param $manufacturerId
     * @return int
     */
    public function countManufacturerProduct($manufacturerId)
    {
        $productManufacturerTable = config('catalog.product_manufacturer_table');
        $productTableName = Config::get('catalog.products_table');

        return DB::table($productManufacturerTable)
            ->select("{$productManufacturerTable}.*")
            ->join($productTableName, "{$productManufacturerTable}.product_id", '=', "{$productTableName}.id")
            ->where("{$productManufacturerTable}.manufacturer_id", $manufacturerId)
            ->where("{$productTableName}.is_active", 1)
            ->count();
    }

    /**
     * Get latest product time.
     *
     * @param $manufacturerId
     * @return Carbon
     */
    public function latestManufacturerProductAddTime($manufacturerId)
    {
        $productManufacturerTable = config('catalog.product_manufacturer_table');
        $productTableName = Config::get('catalog.products_table');

        $product = $this->createModel()->newQuery()
            ->leftJoin($productManufacturerTable, "{$productTableName}.id", '=', "{$productManufacturerTable}.product_id")
            ->where("{$productManufacturerTable}.manufacturer_id", $manufacturerId)
            ->orderBy("{$productTableName}.created_at", 'desc')
            ->first(['created_at']);

        return $product ? $product->created_at : null;
    }

    /**
     * Change product stock number.
     *
     * @param $productId
     * @param $sku
     * @param $number
     * @return mixed
     */
    public function changeProductStockQuantity($productId, $sku, $number)
    {
        $product = $this->find($productId);
        if ($product && $sku && $number) {
            return DB::table(Config::get('catalog.product_items_table'))
                ->where(['product_id' => $productId, 'sku' => $sku])
                ->update(['stock_quantity' => DB::raw('stock_quantity' . ($number > 0 ? '+' : '-') . abs($number)),]);
        }
    }

    /**
     * Get latest products.
     *
     * @param $take
     * @return mixed
     */
    public function getLatestProducts($take)
    {
        return $this->createModel()->latest()->limit($take)->get();
    }

    /**
     * @param $productId
     * @param array $attributes
     * @return array
     */
    public function sortProductAttributes($productId, $attributes)
    {
        if ($this->checkProductAttributes($productId, $attributes) && is_array($attributes)) {
            $attrs = [];
            foreach ($attributes  as $attributeId) {
                $attrs[$attributeId] = $this->attributeService->find($attributeId)->group->id;
            }

            $attributeGroups = $this->find($productId)->attributeGroups;

            usort($attributes, function ($a, $b) use ($attributeGroups, $attrs) {
                foreach ($attributeGroups as $group) {
                    if ($attrs[$a] == $group->id) {
                        return -1;
                    }
                    if ($attrs[$b] == $group->id) {
                        return 1;
                    }
                }
                return 0;
            });
        }

        return $attributes;
    }

    public function generateSKU($categoryId)
    {
        $category = $this->categoryRepository->find($categoryId);
        if ($category) {
            $cids = array_filter(explode('/', $category->path));
            $prefix = '';
            $time = '';
            $number = '';
            if (count($cids) == 1) {
                $category = $this->categoryRepository->find($cids[0]);
                $prefix = substr($category->name, 0, 2);
            } else {
                foreach (array_slice($cids, 0, 2) as $cid) {
                    $category = $this->categoryRepository->find($cid);
                    $prefix .= $category->name[0];
                }
            }
            $carbon = Carbon::now('asia/shanghai');
            $time = ((string)$carbon->year)[3] . sprintf('%02d', $carbon->month);
            $number = (int) Cache::get('product-sku-number-' . $carbon->month);
            if (!$number) {
                $products = app(ProductService::class)->getLatestProducts(1);
                $number = 0;
                if ($products->isNotEmpty()) {
                    if (strlen($products[0]->sku) == 9) {
                        $number = (int)substr($products[0]->sku, -4);
                    } else if (strlen($products[0]->sku) == 11) {
                        $number = (int)substr($products[0]->sku, -6, 4);
                    }
                }
            }
            ++$number;
            $number = sprintf('%04d', $number);
            Cache::put('product-sku-number-' . $carbon->month, $number, $carbon->addMonth());
            return strtoupper($prefix . $time . $number);
        }

        return 'JU1111111';
    }
}