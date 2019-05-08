<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class Attr extends Model
{
    protected $tableConfigKey = 'catalog.attrs_table';

    protected $fillable = [
        'name', 'slug', 'description', 'type', 'is_filterable', 'is_searchable', 'is_viewable', 'sort'
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'is_required' => 'boolean',
        'is_viewable' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(Config::get('catalog.attr_value'), 'attr_id');
    }
}