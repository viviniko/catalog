<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Support\Database\Eloquent\Model;

class ProductAttr extends Model
{
    public $timestamps = false;

    protected $tableConfigKey = 'catalog.product_attr_table';

    protected $fillable = [
        'product_id', 'attr_id',
    ];
}