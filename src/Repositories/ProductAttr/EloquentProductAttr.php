<?php

namespace Viviniko\Catalog\Repositories\ProductAttr;

use Viviniko\Repository\EloquentRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EloquentProductAttr extends EloquentRepository implements ProductAttrRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('catalog.product_attr'));
    }
}