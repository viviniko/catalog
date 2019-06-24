<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class ProductAttr extends Model
{
    public $timestamps = false;

    protected $tableConfigKey = 'catalog.product_attr_table';

    protected $fillable = [
        'product_id', 'attr_id', 'name'
    ];

    public function attr()
    {
        return $this->belongsTo(Config::get('catalog.attr'), 'attr_id');
    }

    public function values()
    {
        return $this->hasMany(Config::get('catalog.product_attr_value'), 'product_attr_id');
    }

    public function getNameAttribute()
    {
        return $this->name ?? $this->attr->name;
    }
}