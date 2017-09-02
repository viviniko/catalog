<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Support\Database\Eloquent\Model;

class SpecificationGroup extends Model
{
    protected $tableConfigKey = 'catalog.specification_groups_table';

    protected $fillable = [
        'category_id', 'name', 'type', 'is_filterable', 'is_searchable', 'is_viewable', 'sort'
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'is_required' => 'boolean',
        'is_viewable' => 'boolean',
        'when' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function specifications()
    {
        return $this->hasMany(Specification::class, 'group_id');
    }

    public function getTitleAttribute()
    {
        return explode('/', $this->name, 2)[0];
    }
}