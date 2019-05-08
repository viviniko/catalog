<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class CategoryAttr extends Model
{
    protected $tableConfigKey = 'catalog.category_attr_table';

    protected $fillable = [
        'category_id', 'attr_id', 'sort'
    ];

    public function category()
    {
        return $this->belongsTo(Config::get('catalog.category'), 'category_id');
    }

    public function attr()
    {
        return $this->belongsTo(Config::get('catalog.attr'), 'attr_id');
    }
}