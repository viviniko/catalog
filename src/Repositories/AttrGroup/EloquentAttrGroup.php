<?php

namespace Viviniko\Catalog\Repositories\AttrGroup;

use Illuminate\Support\Facades\Config;
use Viviniko\Repository\EloquentRepository;

class EloquentAttrGroup extends EloquentRepository implements AttrGroupRepository
{
    protected $searchRules = [
        'name'
    ];

    public function __construct()
    {
        parent::__construct(Config::get('catalog.attr_group'));
    }
}