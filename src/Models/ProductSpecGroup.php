<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductSpecGroup extends Pivot
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'spec_group_id', 'control_type', 'text_prompt', 'is_required', 'sort',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];
}