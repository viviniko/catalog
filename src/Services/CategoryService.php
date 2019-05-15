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
    public function getOne($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    /**
     * @param array $id
     * @return \Illuminate\Support\Collection
     */
    public function getCategoriesByIdIn(array $id);

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