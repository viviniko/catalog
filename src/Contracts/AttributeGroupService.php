<?php

namespace Viviniko\Catalog\Contracts;

interface AttributeGroupService
{
    /**
     * Paginate attribute groups.
     *
     * @param mixed $query
     *
     * @return \Common\Repository\Builder
     */
    public function search($query);

    /**
     * Get attribute group pairs.
     *
     * @return mixed
     */
    public function lists();

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