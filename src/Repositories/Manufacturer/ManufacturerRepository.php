<?php

namespace Viviniko\Catalog\Repositories\Manufacturer;

use Viviniko\Repository\CrudRepository;

interface ManufacturerRepository extends CrudRepository
{
    /**
     * Get manufacturer by given name.
     *
     * @param $name
     *
     * @return mixed
     */
    public function findByName($name);
}