<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class AttrGroup extends Model
{
    protected $tableConfigKey = 'catalog.attr_groups_table';

    protected $fillable = [
        'category_id', 'name', 'slug', 'type', 'is_filterable', 'is_searchable', 'is_viewable', 'sort'
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'is_required' => 'boolean',
        'is_viewable' => 'boolean',
        'when' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Config::get('catalog.category'), 'category_id');
    }

    public function attrs()
    {
        return $this->hasMany(Config::get('catalog.attr'), 'group_id');
    }

    public function getTitleAttribute()
    {
        return explode('/', $this->name, 2)[0];
    }
}