<?php

namespace Viviniko\Catalog\Services\Impl;

use Viviniko\Catalog\Services\SpecService as SpecificationServiceInterface;
use Viviniko\Catalog\Repositories\Spec\SpecRepository;
use Viviniko\Catalog\Repositories\SpecGroup\SpecGroupRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class SpecServiceImpl implements SpecificationServiceInterface
{
    protected $specificationRepository;

    protected $specificationGroupRepository;

    public function __construct(
        SpecRepository $specificationRepository,
        SpecGroupRepository $specificationGroupRepository
    )
    {
        $this->specificationRepository = $specificationRepository;
        $this->specificationGroupRepository = $specificationGroupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return $this->specificationRepository->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function guessByName($name, $groupId = null)
    {
        return Cache::remember("catalog.spec.guess-name?:{$name}", Config::get('cache.ttl', 10), function () use ($name, $groupId) {
            return $this->specificationRepository->guessByName($name, $groupId);
        });
    }


}