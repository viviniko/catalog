<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class Attribute extends Model
{
    protected $tableConfigKey = 'catalog.attributes_table';

    protected $fillable = ['group_id', 'name', 'sort'];

    public function group()
    {
        return $this->belongsTo(Config::get('catalog.attribute_group'), 'group_id');
    }

    public function getValueAttribute()
    {
        return data_get($this->pivot, 'customer_value') ?? $this->title;
    }

    public function getIsSelectedAttribute()
    {
        return data_get($this->pivot, 'is_selected');
    }

    public function getPictureIdAttribute()
    {
        return data_get($this->pivot, 'picture_id');
    }

    public function getPictureAttribute()
    {
        return data_get($this->pivot, 'picture.url');
    }

    public function getTitleAttribute()
    {
        return explode('/', $this->name, 2)[0];
    }
}