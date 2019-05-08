<?php

namespace Viviniko\Catalog\Repositories\Spec;

use Illuminate\Support\Facades\Config;
use Viviniko\Repository\EloquentRepository;

class EloquentSpec extends EloquentRepository implements SpecRepository
{
    public function __construct()
    {
        parent::__construct(Config::get('catalog.spec'));
    }
}