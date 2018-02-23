<?php

namespace Viviniko\Catalog\Contracts;

interface AttributeService
{
    /**
     * @param $id
     * @return mixed
     */
    public function find($id);

    /**
     * Get attribute by name like the given name
     *
     * @param $name
     * @param null $groupId
     * @return mixed
     */
    public function guessByName($name, $groupId = null);
}