<?php

namespace Viviniko\Catalog\Repositories\Attr;

interface AttrRepository
{
    /**
     * Get filterable attributes by given categories.
     *
     * @param mixed $categoryId
     * @return mixed
     */
    public function getFilterableAttrsByCategoryId($categoryId);

    /**
     * Get searchable attributes by given product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getSearchableAttrsByProductId($productId);

    /**
     * Get viewable attributes by given product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getViewableAttrsByProductId($productId);

    /**
     * Find data by id
     *
     * @param       $id
     * @param       $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*']);

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
}