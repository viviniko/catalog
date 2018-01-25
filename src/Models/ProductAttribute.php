<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductAttribute extends Pivot
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'attribute_id', 'customer_value', 'is_selected', 'picture_id',
    ];

    protected $casts = [
        'is_selected' => 'boolean',
    ];

    public function picture()
    {
        return $this->belongsTo(Config::get('media.media'), 'picture_id');
    }
}