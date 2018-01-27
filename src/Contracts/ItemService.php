<?php

namespace Viviniko\Catalog\Contracts;

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
     * @param array $attributes
     * @param array $data
     * @return mixed
     */
    public function createByAttributes($productId, array $attributes, array $data = []);

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
}