<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class ProductSpecValue extends Model
{
    public $timestamps = false;

    protected $tableConfigKey = 'catalog.product_spec_value_table';

    protected $fillable = [
        'product_spec_id', 'spec_value_id', 'name', 'is_selected',
        'image_id', 'swatch_image_id', 'position'
    ];

    protected $casts = [
        'is_selected' => 'boolean',
    ];

    protected $hidden = [
        'specValue',
    ];

    public function getNameAttribute()
    {
        return $this->name ?? $this->specValue->name;
    }

    public function productSpec()
    {
        return $this->belongsTo(Config::get('catalog.product_spec'), 'product_spec_id');
    }

    public function specValue()
    {
        return $this->belongsTo(Config::get('catalog.spec_value'), 'spec_value_id');
    }

    public function image()
    {
        return $this->belongsTo(Config::get('media.file'), 'image_id');
    }

    public function swatchImage()
    {
        return $this->belongsTo(Config::get('media.file'), 'swatch_image_id');
    }
}