<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Facades\Config;

class ProductSpec extends Pivot
{
    public $timestamps = false;

    protected $fillable = [
        'product_id', 'spec_id', 'control_type', 'name', 'is_required', 'sort',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function spec()
    {
        return $this->belongsTo(Config::get('catalog.spec'), 'spec_id');
    }

    public function values()
    {
        return $this->hasMany(Config::get('catalog.product_spec_value'), 'product_spec_id');
    }

    public function getNameAttribute()
    {
        return $this->name ?? $this->spec->name;
    }
}