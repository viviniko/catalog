<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Catalog\Contracts\ProductService;
use Viviniko\Support\Database\Eloquent\Model;

class Manufacturer extends Model
{
    protected $tableConfigKey = 'catalog.manufacturers_table';

    protected $fillable = [
        'name', 'description', 'homepage', 'im', 'sort', 'is_active', 'purchasing_discount', 'product_count',
        'product_update_period', 'product_update_time', 'admin', 'product_type'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'active_product_count'
    ];

    public function getActiveProductCountAttribute()
    {
        return app(ProductService::class)->countManufacturerProduct($this->id);
    }
}