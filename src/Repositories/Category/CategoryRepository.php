<?php

namespace Viviniko\Catalog\Repositories\Category;

interface CategoryRepository
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
     * @param mixed $columns
     * @param bool $recursive
     *
     * @return mixed
     */
    public function getChildren($categoryId, $columns = ['*'], $recursive = false);
}