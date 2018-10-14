<?php

namespace Viviniko\Catalog\Repositories\Category;

interface CategoryRepository
{
    /**
     * Get all categories.
     *
     * @return mixed
     */
    public function all();

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
     * Find all data by id
     *
     * @param       $column
     * @param       $value
     * @param       $columns
     *
     * @return mixed
     */
    public function findAllBy($column, $value = null, $columns = ['*']);

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