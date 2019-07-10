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
        'picture_id', 'swatch_picture_id', 'sort'
    ];

    protected $casts = [
        'is_selected' => 'boolean',
    ];

    protected $hidden = [
        'specValue', 'file', 'swatchFile'
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

    public function picture()
    {
        return $this->belongsTo(Config::get('media.file'), 'picture_id');
    }

    public function getImageAttribute()
    {
        return data_get($this->picture, 'url');
    }

    public function swatchPicture()
    {
        return $this->belongsTo(Config::get('media.file'), 'swatch_picture_id');
    }

    public function getSwatchImageAttribute()
    {
        return data_get($this->swatch_picture, 'url');
    }
}