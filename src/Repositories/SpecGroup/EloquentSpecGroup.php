<?php

namespace Viviniko\Catalog\Repositories\SpecGroup;

use Illuminate\Support\Facades\Config;
use Viviniko\Repository\EloquentRepository;

class EloquentSpecGroup extends EloquentRepository implements SpecGroupRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('catalog.spec_group'));
    }
}