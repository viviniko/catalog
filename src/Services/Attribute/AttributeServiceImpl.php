<?php

namespace Viviniko\Catalog\Services\Attribute;

use Viviniko\Catalog\Contracts\AttributeService as AttributeServiceInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Viviniko\Catalog\Repositories\Attribute\AttributeRepository;
use Viviniko\Catalog\Repositories\AttributeGroup\AttributeGroupRepository;

class AttributeServiceImpl implements AttributeServiceInterface
{
    protected $attributeRepository;

    protected $attributeGroupRepository;

    public function __construct(
        AttributeRepository $attributeRepository,
        AttributeGroupRepository $attributeGroupRepository
    )
    {
        $this->attributeRepository = $attributeRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function guessByName($name, $groupId = null)
    {
        return Cache::remember("catalog.attribute.guess-name?:{$name}", Config::get('cache.ttl', 10), function () use ($name, $groupId) {
            return $this->attributeRepository->guessByName($name, $groupId);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function groups()
    {
        return $this->attributeGroupRepository->pluck('name', 'id');
    }
}