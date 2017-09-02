<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Support\Database\Eloquent\Model;

class ProductPicture extends Model
{
    public $timestamps = false;

    protected $tableConfigKey = 'catalog.product_picture_table';

    protected $fillable = ['product_id', 'picture_id', 'sort'];

    protected $casts = [
        'is_selected' => 'boolean',
    ];
}