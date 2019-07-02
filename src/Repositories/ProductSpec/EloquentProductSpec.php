<?php

namespace Viviniko\Catalog\Repositories\ProductSpec;

use Viviniko\Repository\EloquentRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class EloquentProductSpec extends EloquentRepository implements ProductSpecRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('catalog.product_spec'));
    }
}