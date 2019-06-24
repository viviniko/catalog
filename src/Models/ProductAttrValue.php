<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class ProductAttrValue extends Model
{
    public $timestamps = false;

    protected $tableConfigKey = 'catalog.product_attr_value_table';

    protected $fillable = [
        'product_attr_id', 'attr_value_id', 'name'
    ];

    public function productAttr()
    {
        return $this->belongsTo(Config::get('catalog.product_attr'), 'product_attr_id');
    }

    public function attrValue()
    {
        return $this->belongsTo(Config::get('catalog.attr_value'), 'attr_value_id');
    }

    public function getNameAttribute()
    {
        return $this->name ?? $this->attrValue->name;
    }
}