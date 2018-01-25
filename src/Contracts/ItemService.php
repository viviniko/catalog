<?php

namespace Viviniko\Catalog\Contracts;

interface ItemService
{
    /**
     * Find data by id
     *
     * @param       $id
     *
     * @return mixed
     */
    public function find($id);
}