<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Support\Database\Eloquent\Model;

class ProductSpecification extends Model
{
    public $timestamps = false;

    protected $tableConfigKey = 'catalog.product_specification_table';

    protected $fillable = [
        'product_id', 'specification_id',
    ];
}