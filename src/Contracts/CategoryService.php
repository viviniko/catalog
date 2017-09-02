<?php

namespace Viviniko\Catalog\Contracts;

interface CategoryService
{
    /**
     * Paginate categories.
     *
     * @param mixed $query
     *
     * @return \Common\Repository\Builder
     */
    public function search($query);

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
     * Get all children.
     *
     * @param int $categoryId
     *
     * @return mixed
     */
    public function getAllChildren($categoryId);

    /**
     * Get category groups.
     *
     * @param $categoryId
     * @return mixed
     */
    public function getSpecificationGroups($categoryId);

    /**
     * Get specifications by category id.
     *
     * @param $categoryId
     * @return mixed
     */
    public function getSpecifications($categoryId);
}