<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Support\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class ProductItem extends Model
{
    public $timestamps = false;

    protected $tableConfigKey = 'catalog.product_items_table';

    protected $fillable = [
        'product_id', 'sku', 'upc', 'market_price', 'price', 'weight', 'stock_quantity', 'is_active', 'is_master'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_master' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, Config::get('catalog.product_item_attribute_table'));
    }
}