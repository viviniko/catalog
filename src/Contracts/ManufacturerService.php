<?php

namespace Viviniko\Catalog\Contracts;

interface ManufacturerService
{
    /**
     * Paginate manufacturers.
     *
     * @param mixed $query
     *
     * @return \Viviniko\Repository\Builder
     */
    public function search($query);

    /**
     * Find manufacturer by its id.
     *
     * @param $id
     * @return mixed
     */
    public function find($id);

    /**
     * Create new manufacturer.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update manufacturer specified by it's id.
     *
     * @param $id
     * @param array $data
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Delete manufacturer with provided id.
     *
     * @param $id
     * @return mixed
     */
    public function delete($id);

    /**
     * Pluck manufacturer.
     *
     * @param string $column
     * @param string $key
     * @return mixed
     */
    public function pluck($column, $key = null);

    public function findByName($name);
}