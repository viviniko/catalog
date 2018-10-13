<?php

namespace Viviniko\Catalog\Repositories\Manufacturer;

use Viviniko\Repository\SearchRequest;

interface ManufacturerRepository
{
    /**
     * Search.
     *
     * @param SearchRequest $searchRequest
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Collection
     */
    public function search(SearchRequest $searchRequest);

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