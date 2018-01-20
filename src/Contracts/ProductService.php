<?php

namespace Viviniko\Catalog\Contracts;

use Carbon\Carbon;

interface ProductService
{
    /**
     * Paginate products.
     *
     * @param mixed $query
     *
     * @return \Laravel\Scout\Builder
     */
    public function search($query);

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param null $perPage
     * @param null $search
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $search = null);

    /**
     * Find data by id
     *
     * @param       $id
     *
     * @return mixed
     */
    public function find($id);

    /**
     * Save a new entity in repository
     *
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update a entity in repository by id
     *
     * @param       $id
     * @param array $data
     *
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Set product status.
     *
     * @param $productId
     * @param $status
     * @return mixed
     */
    public function changeProductStatus($productId, $status);

    /**
     * Delete a entity in repository by id
     *
     * @param $id
     *
     * @return int
     */
    public function delete($id);

    /**
     * Get product item.
     *
     * @param $productId
     * @param $productItemId
     * @return mixed
     */
    public function findProductItem($productId, $productItemId);

    /**
     * Create product item.
     *
     * @param $productId
     * @param array $specifications
     *
     * @return mixed
     */
    public function createProductItem($productId, array $specifications, $index=1);

    /**
     * Update product item.
     *
     * @param $productId
     * @param $productItemId
     * @param array $data
     *
     * @return mixed
     */
    public function updateProductItem($productId, $productItemId, array $data);

    /**
     * Delete product item.
     *
     * @param $productId
     * @param $productItemId
     *
     * @return mixed
     */
    public function deleteProductItem($productId, $productItemId);

    /**
     * Sync attribute groups.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function attachAttributeGroup($productId, array $data);

    /**
     * Update attribute group.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function updateAttributeGroup($productId, array $data);

    /**
     * Detach attribute group.
     *
     * @param $productId
     * @param $specificationGroupId
     *
     * @return mixed
     */
    public function detachAttributeGroup($productId, $specificationGroupId);

    /**
     * Attach attribute.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function attachAttribute($productId, array $data);

    /**
     * Update attribute.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function updateAttribute($productId, array $data);

    /**
     * Detach attribute.
     *
     * @param $productId
     * @param $specificationId
     *
     * @return mixed
     */
    public function detachAttribute($productId, $specificationId);

    /**
     * Detach picture.
     *
     * @param $pictureId
     *
     * @return mixed
     */
    public function detachPicture($pictureId);

    /**
     * Get attribute groups by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getAttributeGroups($productId);

    /**
     * Get attributes by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getAttributes($productId);

    /**
     * Get specifications by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getSpecifications($productId);

    /**
     * Get product item.
     *
     * @param $productId
     * @param array $attributes
     * @return mixed
     */
    public function getProductItem($productId, array $attributes);

    /**
     * Get product picture.
     *
     * @param $productId
     * @param $attributes
     * @return mixed
     */
    public function getProductPicture($productId, array $attributes);

    /**
     * Get product sku.
     *
     * @param $productId
     * @param $attributes
     * @return mixed
     */
    public function getProductSku($productId, $attributes);

    /**
     * Get product sku id.
     *
     * @param $productId
     * @param $attributes
     * @return int|string
     */
    public function getProductSkuId($productId, $attributes);

    /**
     * Count manufacturer product.
     *
     * @param $manufacturerId
     * @return int
     */
    public function countManufacturerProduct($manufacturerId);

    /**
     * Get latest product time.
     *
     * @param $manufacturerId
     * @return Carbon
     */
    public function latestManufacturerProductAddTime($manufacturerId);

    /**
     * Change product stock number.
     *
     * @param $productId
     * @param $sku
     * @param $quantity
     * @return mixed
     */
    public function changeProductStockQuantity($productId, $sku, $quantity);

    /**
     * Get latest products.
     *
     * @param $take
     * @return mixed
     */
    public function getLatestProducts($take);

    /**
     * @param $productId
     * @param mixed $attributes
     * @return array
     */
    public function sortProductAttributes($productId, $attributes);

    public function generateSKU($categoryId);
}