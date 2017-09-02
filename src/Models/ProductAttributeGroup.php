<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductAttributeGroup extends Pivot
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'attribute_group_id', 'control_type', 'text_prompt', 'is_required', 'when', 'sort',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];
}