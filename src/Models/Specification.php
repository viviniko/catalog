<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class Specification extends Model
{
    protected $tableConfigKey = 'catalog.specifications_table';

    protected $fillable = ['group_id', 'name', 'sort'];

    public function group()
    {
        return $this->belongsTo(Config::get('catalog.specification_group'), 'group_id');
    }

    public function getTitleAttribute()
    {
        return explode('/', $this->name, 2)[0];
    }

    public function getSlugAttribute()
    {
        return str_slug($this->title);
    }
}