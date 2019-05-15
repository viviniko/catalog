<?php

namespace Viviniko\Catalog\Repositories\Product;

use Viviniko\Repository\CrudRepository;

interface ProductRepository extends CrudRepository
{
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