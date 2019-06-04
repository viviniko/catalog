<?php

namespace Viviniko\Catalog\Services;

interface ProductService
{
    /**
     * Paginate the given query into a simple paginator.
     *
     * @param $pageSize
     * @param array $wheres
     * @param array $orders
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($pageSize, $wheres = [], $orders = []);

    /**
     * Search products.
     *
     * @param null $keyword
     * @param null $filters
     * @param null $order
     * @param null $except
     * @param array $categories
     * @return \Laravel\Scout\Builder
     */
    public function search($keyword = null, $filters = null, $order = null, $except = null, $categories = []);

    /**
     * Find data by id
     *
     * @param       $id
     *
     * @return mixed
     */
    public function getProduct($id);

    /**
     * Save a new entity in repository
     *
     * @param array $data
     *
     * @return mixed
     */
    public function createProduct(array $data);

    /**
     * Update a entity in repository by id
     *
     * @param       $id
     * @param array $data
     *
     * @return mixed
     */
    public function updateProduct($id, array $data);

    /**
     * Delete a entity in repository by id
     *
     * @param $id
     *
     * @return int
     */
    public function deleteProduct($id);

    /**
     * Attach attribute.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function attachProductSpec($productId, array $data);

    /**
     * Update attribute.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function updateProductSpec($productId, array $data);

    /**
     * Detach attribute.
     *
     * @param $productId
     * @param $specificationId
     *
     * @return mixed
     */
    public function detachProductSpec($productId, $specificationId);

    /**
     * Detach product picture.
     *
     * @param $pictureId
     * @return mixed
     */
    public function detachProductPicture($pictureId);

    /**
     * @param array $specifications
     * @param null $x
     * @param null $y
     * @return mixed
     */
    public function addProductSpecSwatchPicture(array &$specifications, $x = null, $y = null);

    /**
     * @param $productId
     * @param $specificationId
     * @param $pictureId
     * @param $x
     * @param $y
     * @return mixed
     */
    public function updateProductSpecSwatchPicture($productId, $specificationId, $pictureId, $x, $y);

    /**
     * @param $productId
     * @return mixed
     */
    public function getProductSearchableMapping();

    /**
     * @param $productId
     * @return mixed
     */
    public function getProductSearchableArray($productId);

    /**
     * @param $productId
     * @return bool
     */
    public function isProductCanSearchable($productId);

    /**
     * @param $productId
     * @return mixed
     */
    public function getProductSwatchPictures($productId);

    /**
     * @param $productId
     *
     * @return mixed
     */
    public function generateProductItems($productId);
}