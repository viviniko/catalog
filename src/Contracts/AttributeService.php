<?php

namespace Viviniko\Catalog\Contracts;

interface AttributeService
{
    /**
     * Get attribute by name like the given name
     *
     * @param $name
     * @param null $groupId
     * @return mixed
     */
    public function guessByName($name, $groupId = null);
}