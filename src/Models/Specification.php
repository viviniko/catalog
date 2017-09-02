<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Support\Database\Eloquent\Model;

class Specification extends Model
{
    protected $tableConfigKey = 'catalog.specifications_table';

    protected $fillable = ['group_id', 'name', 'sort'];

    public function group()
    {
        return $this->belongsTo(SpecificationGroup::class, 'group_id');
    }

    public function getTitleAttribute()
    {
        return explode('/', $this->name, 2)[0];
    }
}