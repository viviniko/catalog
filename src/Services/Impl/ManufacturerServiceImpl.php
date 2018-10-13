<?php

namespace Viviniko\Catalog\Services\Impl;

use Illuminate\Http\Request;
use Viviniko\Catalog\Repositories\Manufacturer\ManufacturerRepository;
use Viviniko\Catalog\Services\ManufacturerService;
use Viviniko\Support\AbstractRequestRepositoryService;

class ManufacturerServiceImpl extends AbstractRequestRepositoryService implements ManufacturerService
{
    protected $searchRules = [
        'id',
        'name' => "like",
        'product_type' => 'like',
        'product_update_period' => 'like',
        'product_update_time' => 'betweenDate',
        'is_active',
        'admin' => 'like'
    ];

    protected $repository;

    public function __construct(ManufacturerRepository $repository, Request $request)
    {
        parent::__construct($request);
        $this->repository = $repository;
    }

    public function getRepository()
    {
        return $this->repository;
    }
}