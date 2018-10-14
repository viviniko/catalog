<?php

namespace Viviniko\Catalog\Services;

interface CategoryService
{
    public function categories();

    public function createCategory(array $data);

    public function updateCategory($id, array $data);

    public function deleteCategory($id);

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
    public function getCategoryChildren($categoryId, $recursive = true);
}