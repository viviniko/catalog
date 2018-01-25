<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class ManufacturerProduct extends Model
{
    protected $tableConfigKey = 'catalog.manufacturer_products_table';

    public $timestamps = false;

    protected $fillable = [
        'product_id', 'manufacturer_id', 'url', 'price', 'sku', 'name',
    ];

    public function manufacturer()
    {
        return $this->belongsTo(Config::get('catalog.manufacturer'), 'manufacturer_id');
    }

    public function getManufacturerNameAttribute()
    {
        return data_get($this->manufacturer, 'name');
    }
}