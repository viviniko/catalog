<?php

namespace Viviniko\Catalog\Repositories\Manufacturer;

interface ManufacturerRepository
{
    /**
     * Paginate the given query into a simple paginator.
     *
     * @param null $perPage
     * @param string $searchName
     * @param null $search
     * @param null $order
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage, $searchName = 'search', $search = null, $order = null);

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
     * Manufacturer lists.
     *
     * @param string $column
     * @param string $key
     * @return mixed
     */
    public function lists($column = 'name', $key = null);

    /**
     * Get manufacturer by given name.
     *
     * @param $name
     *
     * @return mixed
     */
    public function findByName($name);
}