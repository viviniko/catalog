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
        'specValue'
    ];

    public function getNameAttribute()
    {
        return $this->name ?? $this->specValue->name;
    }

    public function specValue()
    {
        return $this->belongsTo(Config::get('catalog.spec_value'), 'spec_value_id');
    }

    public function file()
    {
        return $this->belongsTo(Config::get('media.file'), 'picture_id');
    }

    public function picture()
    {
        return data_get($this->file, 'url');
    }

    public function swatchFile()
    {
        return $this->belongsTo(Config::get('media.file'), 'swatch_picture_id');
    }

    public function swatchPicture()
    {
        return data_get($this->swatch_file, 'url');
    }
}