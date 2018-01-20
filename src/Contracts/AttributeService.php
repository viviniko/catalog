<?php

namespace Viviniko\Catalog\Contracts;

interface AttributeService
{
    /**
     * Get attribute by its group id.
     *
     * @param $groupId
     * @return mixed
     */
    public function findByGroupId($groupId);

    /**
     * Get attributes by all id given.
     *
     * @param $ids
     * @return mixed
     */
    public function findIn($ids);

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
     * Get attribute by name like the given name
     *
     * @param $name
     * @param null $groupId
     * @return mixed
     */
    public function guessByName($name, $groupId = null);
}