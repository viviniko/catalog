<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
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

    public function products()
    {
        return $this->hasMany(Config::get('catalog.manufacturer_product'), 'manufacturer_id');
    }

    public function getActiveProductCountAttribute()
    {
        return $this->products()
            ->join(Config::get('catalog.products_table'), Config::get('catalog.manufacturer_product') . '.product_id', '=', Config::get('catalog.products_table') . '.id')
            ->where(Config::get('catalog.products_table').'.is_active', true)
            ->count();
    }
}