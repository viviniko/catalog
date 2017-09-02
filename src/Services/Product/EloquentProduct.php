<?php

namespace Viviniko\Catalog\Services\Product;

use Carbon\Carbon;
use Viviniko\Catalog\Contracts\AttributeService;
use Viviniko\Catalog\Contracts\CategoryService;
use Viviniko\Catalog\Contracts\ProductService as ProductServiceInterface;
use Viviniko\Catalog\Contracts\SpecificationService;
use Viviniko\Catalog\Models\Attribute;
use Viviniko\Media\Contracts\ImageService;
use Viviniko\Repository\SimpleRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EloquentProduct extends SimpleRepository implements ProductServiceInterface
{
    protected $modelConfigKey = 'catalog.product';

    /**
     * @var \Viviniko\Catalog\Contracts\CategoryService
     */
    protected $categoryService;

    /**
     * @var \Viviniko\Catalog\Contracts\AttributeService
     */
    protected $attributeService;

    /**
     * @var \Viviniko\Catalog\Contracts\SpecificationService
     */
    protected $specificationService;

    /**
     * @var \Viviniko\Media\Contracts\ImageService
     */
    protected $imageService;

    /**
     * EloquentProduct constructor.
     * @param \Viviniko\Catalog\Contracts\CategoryService $categoryService
     * @param \Viviniko\Catalog\Contracts\AttributeService $attributeService
     * @param \Viviniko\Catalog\Contracts\SpecificationService $specificationService
     * @param \Viviniko\Media\Contracts\ImageService $imageService
     */
    public function __construct(
        CategoryService $categoryService,
        AttributeService $attributeService,
        SpecificationService $specificationService,
        ImageService $imageService)
    {
        $this->categoryService = $categoryService;
        $this->attributeService = $attributeService;
        $this->specificationService = $specificationService;
        $this->imageService = $imageService;
    }

    public function search($keywords)
    {
        $productTable = Config::get('catalog.products_table');
        $productManufacturerTable = Config::get('catalog.product_manufacturer_table');
        $productItemsTable = Config::get('catalog.product_items_table');
        $manufacturerTable = Config::get('catalog.manufacturers_table');
        $categoryTable = Config::get('catalog.categories_table');

        $this->fieldSearchable = [
            'id' => "{$productTable}.id:=",
            'name' => "{$productTable}.name:like",
            'category' => "{$categoryTable}.name:like",
            'sku' => 'like',
            'manufacturer' => "{$manufacturerTable}.name:like",
            'product_origin_sku' => "{$productManufacturerTable}.product_origin_sku:like",
            'price' => "{$productItemsTable}.price:=",
            'stock_quantity' => "{$productItemsTable}.stock_quantity:=",
            'created_at' => 'betweenDate',
            'updated_at' => 'betweenDate',
            'created_by' => 'like',
            'updated_by' => 'like',
            'is_active' => "{$productTable}.is_active:=",
        ];

        $builder = parent::search($keywords)->select(["{$productTable}.*"])
            ->join($categoryTable, "{$productTable}.category_id", '=', "{$categoryTable}.id", 'left')
            ->join($productManufacturerTable, "{$productTable}.id", '=', "{$productManufacturerTable}.product_id", 'left')
            ->join($manufacturerTable, "{$manufacturerTable}.id", '=', "{$productManufacturerTable}.manufacturer_id", 'left')
            ->join($productItemsTable, "{$productTable}.id", '=', "{$productItemsTable}.product_id", 'left')
            ->where("{$productItemsTable}.is_master", true);

        $builder->orderBy('created_at', 'desc');

        return $builder;
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param null $perPage
     * @param null $keyword
     * @param null $categoryId
     * @param null $attributes
     * @param null $order
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $keyword = null, $categoryId = null, $attributes = null, $order = null)
    {
        $builder = $this->createModel()->search($keyword);

        if ($categoryId) {
            $builder->where('category_id', $this->categoryService->getAllChildren($categoryId)->pluck('id')->prepend($categoryId)->toArray());
        }

        if (!empty($attributes)) {
            $attributes = array_unique($attributes);
            $builder->where('term.specifications', $attributes);
        }

        if (!empty($order)) {
            if (!is_array($order)) {
                $order = [$order, 'desc'];
            }
            $builder->orderBy(...$order);
        }

        $builder->orderBy('sort', 'desc');

        return $builder->paginate($perPage);

//        $query = $this->createModel()->newQuery()->with('master');
//
//        $productTableName = Config::get('catalog.products_table');
//        $productItemTable = Config::get('catalog.product_items_table');
//        $productSpecificationTableName = Config::get('catalog.product_specification_table');
//
//        $query->select(["{$productTableName}.*"]);
//
//        $query->join($productItemTable, "{$productTableName}.id", '=', "{$productItemTable}.product_id")->where("{$productItemTable}.is_master", true);
//
//        if ($keyword) {
//
//        }
//
//        if ($categoryId) {
//            $query->whereIn('category_id', $this->categoryService->getAllChildren($categoryId)->pluck('id')->prepend($categoryId)->toArray());
//        }
//
//        if (!empty($attributes)) {
//            $attributes = array_unique($attributes);
//            $query->whereIn("{$productTableName}.id", function ($subQuery) use ($productSpecificationTableName, $attributes) {
//                $subQuery->select('product_id')
//                    ->from($productSpecificationTableName)
//                    ->whereIn('specification_id', $attributes)
//                    ->groupBy("product_id")
//                    ->havingRaw("count(product_id)=" . count($attributes));;
//            });
//        }
//
//        if (!empty($order)) {
//            if (!is_array($order)) {
//                $order = [$order, 'desc'];
//            }
//            $query->orderBy(...$order);
//        }
//
//        $result = $query->distinct()->paginate($perPage);
//
//        $result->appends(request()->all());
//
//        return $result;
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param null $take
     * @param null $keyword
     * @param null $categoryId
     * @param null $attributes
     * @param null $order
     * @return \Illuminate\Support\Collection
     */
    public function getProducts($take, $keyword = null, $categoryId = null, $attributes = null, $order = null)
    {
        $builder = $this->createModel()->search($keyword);

        if ($categoryId) {
            $builder->where('category_id', $this->categoryService->getAllChildren($categoryId)->pluck('id')->prepend($categoryId)->toArray());
        }

        if (!empty($attributes)) {
            $attributes = array_unique($attributes);
            $builder->where('specifications', $attributes);
        }

        if (!empty($order)) {
            if (!is_array($order)) {
                $order = [$order, 'desc'];
            }
            $builder->orderBy(...$order);
        }

        $builder->orderBy('sort', 'desc');

        return $builder->take($take)->get();
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

            $data['sku'] = isset($data['sku']) ? (string)$data['sku'] : '';
            $data['is_master'] = true;
            $data['upc'] = isset($data['upc']) ? (string)$data['upc'] : '';
            $data['market_price'] = isset($data['market_price']) ? (float)$data['market_price'] : 0;
            $data['stock_quantity'] = isset($data['stock_quantity']) ? (int)$data['stock_quantity'] : 0;
            $product->items()->updateOrCreate(['product_id' => $product->id, 'is_master' => true], $data);

            if (!empty($data['manufacturer_id'])) {
                $product->manufacturer()->updateOrCreate([
                    'product_id' => $product->id,
                    'manufacturer_id' => $data['manufacturer_id']
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

            if (!empty($data['specifications'])) {
                $product->specifications()->sync($data['specifications']);
            }

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
        });


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
    public function createProductItem($productId, array $attributes)
    {
        $productItem = null;

        DB::transaction(function () use ($productId, $attributes, &$productItem) {
            $product = $this->find($productId);
            $productItem = $product->items()->create([
                'sku' => '',
                'upc' => '',
                'price' => 0,
                'stock_quantity' => 0,
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
        $data['upc'] = data_get($data, 'upc');
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
                $product->attributes()->updateExistingPivot($attributeId, $attributes);
            });
        }

        return $product;
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
        $product = $this->find($productId);
        return $product ? $product->attributeGroups : collect([]);
    }

    /**
     * Get attributes by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getAttributes($productId)
    {
        $product = $this->find($productId);
        return $product ? $product->attributes : collect([]);
    }

    /**
     * Get specifications by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getSpecifications($productId)
    {
        return $this->specificationService->findIn(
            DB::table(Config::get('catalog.product_specification_table'))
                ->where('product_id', $productId)
                ->pluck('specification_id')
                ->toArray()
        );
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
            ->select("$productItemTable.*")
            ->where('product_id', $productId)
            ->join($productItemAttributeTable, "$productItemTable.id", '=', "$productItemAttributeTable.product_item_id")
            ->whereIn("$productItemAttributeTable.attribute_id", $attributes)
            ->groupBy("$productItemTable.id")
            ->havingRaw("count($productItemTable.id)=" . count($attributes))
            ->first();
        if (!$productItem) {
            $productItem = DB::table($productItemTable)->where(['product_id' => $productId, 'is_master' => '1'])->first();
        }

        return $productItem;
    }

    public function resetProductSelectedAttribute($productId, $attributeId)
    {
        $attribute = $this->attributeService->find($attributeId);
        DB::table(Config::get('catalog.product_attribute_table'))->where('product_id', $productId)->whereIn('attribute_id', function ($query) use ($attribute) {
            $query->select('id')->from(Config::get('catalog.attributes_table'))->where('group_id', $attribute->group_id);
        })->update(['is_selected' => 0]);
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
                ->distinct()
                ->pluck('picture_id');
            if ($pictureIds->isNotEmpty() && ($image = $this->imageService->find($pictureIds->first()))) {
                return $image->url;
            }
        }

        return data_get($this->find($productId), 'cover');
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
     * Get products by all id given.
     *
     * @param $ids
     * @return mixed
     */
    public function findIn($ids)
    {
        return $this->createModel()->newQuery()->whereIn('id', $ids)->get();
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

        return DB::table($productManufacturerTable)->where('manufacturer_id', $manufacturerId)->count();
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
}