<?php

namespace Viviniko\Catalog\Services;

interface ManufacturerService
{
    /**
     * Paginate the given query into a simple paginator.
     *
     * @param null $pageSize
     * @param array $wheres
     * @param array $orders
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($pageSize = null, $wheres = [], $orders = []);
}