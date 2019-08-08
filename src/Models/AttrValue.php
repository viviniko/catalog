<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class AttrValue extends Model
{
    protected $tableConfigKey = 'catalog.attr_values_table';

    protected $fillable = ['attr_id', 'name', 'slug', 'description', 'position'];

    public function attr()
    {
        return $this->belongsTo(Config::get('catalog.attr'), 'attr_id');
    }
}