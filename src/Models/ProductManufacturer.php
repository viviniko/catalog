<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Support\Database\Eloquent\Model;

class ProductManufacturer extends Model
{
    protected $tableConfigKey = 'catalog.product_manufacturer_table';

    public $timestamps = false;

    protected $fillable = [
        'product_id', 'manufacturer_id', 'purchasing_url', 'purchasing_price', 'product_origin_sku', 'product_origin_name',
    ];

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class, 'manufacturer_id');
    }

    public function getManufacturerNameAttribute()
    {
        return data_get($this->manufacturer, 'name');
    }
}