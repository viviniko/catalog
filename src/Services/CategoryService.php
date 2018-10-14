<?php

namespace Viviniko\Catalog\Services;

interface CategoryService
{
    public function all();

    /**
     * Find data by id
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function getCategory($id);

    /**
     * @param array $id
     * @return \Illuminate\Support\Collection
     */
    public function getCategoryByIdIn(array $id);

    /**
     * Get all children.
     *
     * @param int $categoryId
     * @param bool $recursive
     *
     * @return \Illuminate\Support\Collection
     */
    public function getChildren($categoryId, $recursive = true);
}