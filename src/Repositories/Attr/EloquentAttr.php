<?php

namespace Viviniko\Catalog\Repositories\Attr;

use Illuminate\Support\Facades\Config;
use Viviniko\Repository\EloquentRepository;

class EloquentAttr extends EloquentRepository implements AttrRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('catalog.attr'));
    }
}