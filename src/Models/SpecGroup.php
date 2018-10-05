<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class SpecGroup extends Model
{
    protected $tableConfigKey = 'catalog.spec_groups_table';

    protected $fillable = [
        'name', 'slug', 'sort'
    ];

    public function specs()
    {
        return $this->hasMany(Config::get('catalog.spec'), 'group_id');
    }

    public function getTitleAttribute()
    {
        return explode('/', $this->name, 2)[0];
    }
}