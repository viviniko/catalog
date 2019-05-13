<?php

namespace Viviniko\Catalog\Services\Impl;

use Viviniko\Catalog\Repositories\SpecValue\SpecValueRepository;
use Viviniko\Catalog\Services\SpecService as SpecificationServiceInterface;
use Viviniko\Catalog\Repositories\Spec\SpecRepository;

class SpecServiceImpl implements SpecificationServiceInterface
{
    protected $specificationRepository;

    protected $specValueRepository;

    public function __construct(
        SpecRepository $specificationRepository,
        SpecValueRepository $specValueRepository
    )
    {
        $this->specificationRepository = $specificationRepository;
        $this->specValueRepository = $specValueRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getSpec($id)
    {
        return $this->specificationRepository->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function guessByName($name, $groupId = null)
    {
        return $this->specificationRepository->guessByName($name, $groupId);
    }

    /**
     * {@inheritdoc}
     */
    public function allGroups()
    {
        return $this->specificationGroupRepository->all();
    }

    /**
     * {@inheritdoc}
     */
    public function listGroups($name = 'name', $key = 'id')
    {
        return $this->specificationGroupRepository->all([$name, $key])->pluck($name, $key);
    }

    /**
     * {@inheritdoc}
     */
    public function createSpec(array $data)
    {
        return $this->specificationRepository->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateSpec($specId, array $data)
    {
        return $this->specificationRepository->update($specId, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteSpec($specId)
    {
        return $this->specificationRepository->delete($specId);
    }

    /**
     * {@inheritdoc}
     */
    public function createGroup(array $data)
    {
        return $this->specificationGroupRepository->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroup($specGroupId, array $data)
    {
        return $this->specificationGroupRepository->update($specGroupId, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteGroup($specGroupId)
    {
        return $this->specificationGroupRepository->delete($specGroupId);
    }

    /**
     * {@inheritdoc}
     */
    public function getSpecsByGroupId($groupId)
    {
        return $this->specificationRepository->findAllBy('group_id', $groupId);
    }
}