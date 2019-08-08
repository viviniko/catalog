<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class ProductSpec extends Model
{
    public $timestamps = false;

    protected $tableConfigKey = 'catalog.product_spec_table';

    protected $fillable = [
        'product_id', 'spec_id', 'control_type', 'name', 'is_required', 'position',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    protected $hidden = [
        'spec'
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