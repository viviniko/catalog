<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class Spec extends Model
{
    protected $tableConfigKey = 'catalog.specs_table';

    protected $fillable = [
        'name', 'slug', 'description', 'sort'
    ];

    public function values()
    {
        return $this->hasMany(Config::get('catalog.spec_value'), 'spec_id');
    }
}