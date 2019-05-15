<?php

namespace Viviniko\Catalog\Repositories\Manufacturer;

use Viviniko\Repository\CrudRepository;

interface ManufacturerRepository extends CrudRepository
{
    /**
     * Manufacturer lists.
     *
     * @param string $column
     * @param string $key
     * @return mixed
     */
    public function lists($column = 'name', $key = null);

    /**
     * Get manufacturer by given name.
     *
     * @param $name
     *
     * @return mixed
     */
    public function findByName($name);
}