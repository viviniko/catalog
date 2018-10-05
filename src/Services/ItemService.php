<?php

namespace Viviniko\Catalog\Services;

interface ItemService
{
    /**
     * Find data by id
     *
     * @param       $id
     *
     * @return mixed
     */
    public function find($id);

    /**
     * @param $productId
     * @param array $specifications
     * @param array $data
     * @return mixed
     */
    public function createBySpecs($productId, array $specifications, array $data = []);

    /**
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id, $data);

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * @param $productId
     * @return mixed
     */
    public function deleteByProductId($productId);

    /**
     * Get item.
     *
     * @param $productId
     * @param array $specifications
     * @return mixed
     */
    public function findByProductSpecs($productId, array $specifications);
}