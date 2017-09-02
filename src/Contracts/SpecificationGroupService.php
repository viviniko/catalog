<?php

namespace Viviniko\Catalog\Contracts;

interface SpecificationGroupService
{
    /**
     * Get groups by category id.
     *
     * @param $categoryId
     * @return mixed
     */
    public function findByCategoryId($categoryId);

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