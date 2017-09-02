<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Support\Database\Eloquent\Model;

class AttributeGroup extends Model
{
    protected $tableConfigKey = 'catalog.attribute_groups_table';

    protected $fillable = [
        'name', 'sort'
    ];

    public function attributes()
    {
        return $this->hasMany(Attribute::class, 'group_id');
    }

    public function getTextPromptAttribute()
    {
        return data_get($this->pivot, 'text_prompt') ?? $this->title;
    }

    public function getControlTypeAttribute()
    {
        return data_get($this->pivot, 'control_type');
    }

    public function getIsRequiredAttribute()
    {
        return data_get($this->pivot, 'is_required');
    }

    public function getTitleAttribute()
    {
        return explode('/', $this->name, 2)[0];
    }
}