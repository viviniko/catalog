<?php

namespace Viviniko\Catalog\Repositories\ProductSpecValue;

use Viviniko\Repository\EloquentRepository;
use Illuminate\Support\Facades\Config;

class EloquentProductSpecValue extends EloquentRepository implements ProductSpecValueRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('catalog.product_spec_value'));
    }
}