<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class Attr extends Model
{
    protected $tableConfigKey = 'catalog.attrs_table';

    protected $fillable = ['group_id', 'name', 'slug', 'sort'];

    public function group()
    {
        return $this->belongsTo(Config::get('catalog.attr_group'), 'group_id');
    }

    public function getTitleAttribute()
    {
        return explode('/', $this->name, 2)[0];
    }
}