<?php

namespace Viviniko\Catalog\Services;

interface SpecService
{
    public function allGroups();

    public function listGroups($name = 'name', $key = 'id');

    public function createGroup(array $data);

    public function updateGroup($specGroupId, array $data);

    public function deleteGroup($specGroupId);

    public function getSpecsByGroupId($groupId);

    public function createSpec(array $data);

    public function updateSpec($specId, array $data);

    public function deleteSpec($specId);

    /**
     * @param $id
     * @return mixed
     */
    public function getSpec($id);

    /**
     * Get attribute by name like the given name
     *
     * @param $name
     * @param null $groupId
     * @return mixed
     */
    public function guessByName($name, $groupId = null);
}