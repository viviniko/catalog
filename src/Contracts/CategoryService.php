<?php

namespace Viviniko\Catalog\Contracts;

use Illuminate\Support\Collection;

interface CategoryService
{
    /**
     * Find data by id
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function find($id);

    /**
     * Get all children id.
     *
     * @param int $id
     * @param bool $recursive
     *
     * @return Collection
     */
    public function getChildrenId($id, $recursive = true);
}