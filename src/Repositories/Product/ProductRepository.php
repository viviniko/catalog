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
     * Create product item.
     *
     * @param $productId
     * @param array $attributes
     *
     * @return mixed
     */
    public function createProductItem($productId, array $attributes);

    /**
     * Set product status.
     *
     * @param $productId
     * @param $status
     * @return int
     */
    public function changeProductStatus($productId, $status);

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