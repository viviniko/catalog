<?php

namespace Viviniko\Catalog\Repositories\SpecValue;

use Illuminate\Support\Facades\Config;
use Viviniko\Repository\EloquentRepository;

class EloquentSpecValue extends EloquentRepository implements SpecValueRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('catalog.spec_value'));
    }
}