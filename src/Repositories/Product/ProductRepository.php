<?php

namespace Viviniko\Catalog\Repositories\Product;

interface ProductRepository
{
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
     * Delete a entity in repository by id
     *
     * @param $id
     *
     * @return int
     */
    public function delete($id);

    /**
     * Attach attribute groups.
     *
     * @param $productId
     * @param $attributeGroupId
     * @param array $attributes
     *
     * @return mixed
     */
    public function attachProductAttributeGroup($productId, $attributeGroupId, array $attributes = []);

    /**
     * Update attribute group.
     *
     * @param $productId
     * @param $attributeGroupId
     * @param array $attributes
     *
     * @return mixed
     */
    public function updateProductAttributeGroup($productId, $attributeGroupId, array $attributes = []);

    /**
     * Detach attribute group.
     *
     * @param $productId
     * @param $specificationGroupId
     *
     * @return mixed
     */
    public function detachProductAttributeGroup($productId, $specificationGroupId);

    /**
     * @param $productId
     * @param $attributeId
     * @return mixed
     */
    public function resetProductSelectedAttribute($productId, $attributeId);

    /**
     * Get latest products.
     *
     * @param $take
     * @param $columns
     *
     * @return mixed
     */
    public function getLatestProducts($take, $columns = ['*']);
}