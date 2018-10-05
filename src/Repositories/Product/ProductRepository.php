<?php

namespace Viviniko\Catalog\Repositories\Product;

interface ProductRepository
{
    /**
     * Paginate the given query into a simple paginator.
     *
     * @param null $perPage
     * @param string $searchName
     * @param null $search
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $searchName = 'search', $search = null);

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
     * Attach specification groups.
     *
     * @param $productId
     * @param $specificationGroupId
     * @param array $specifications
     *
     * @return mixed
     */
    public function attachProductSpecGroup($productId, $specificationGroupId, array $specifications = []);

    /**
     * Update specification group.
     *
     * @param $productId
     * @param $specificationGroupId
     * @param array $specifications
     *
     * @return mixed
     */
    public function updateProductSpecGroup($productId, $specificationGroupId, array $specifications = []);

    /**
     * Detach specification group.
     *
     * @param $productId
     * @param $specificationGroupId
     *
     * @return mixed
     */
    public function detachProductSpecGroup($productId, $specificationGroupId);

    /**
     * @param $productId
     * @param $specificationId
     * @return mixed
     */
    public function resetProductSelectedSpec($productId, $specificationId);

    /**
     * @param $productId
     * @return mixed
     */
    public function getProductSpec($productId);

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