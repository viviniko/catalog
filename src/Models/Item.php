<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Cart\Contracts\CartItem;
use Viviniko\Catalog\Facades\ProductSpecs;
use Viviniko\Catalog\Facades\ProductSpecValues;
use Viviniko\Currency\Money;
use Viviniko\Support\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Item extends Model implements CartItem
{
    public $timestamps = false;

    protected $tableConfigKey = 'catalog.items_table';

    protected $fillable = [
        'product_id', 'product_specs', 'product_spec_names', 'sku', 'price', 'discount', 'weight', 'image_id',
        'inventory_quantity', 'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'product_specs' => 'array',
        'product_spec_names' => 'array',
    ];

    protected $hidden = [
        'is_primary', 'weight', 'image_id',
    ];

    public function product()
    {
        return $this->belongsTo(Config::get('catalog.product'), 'product_id');
    }

    public function getSpecValuesAttribute()
    {
        return ProductSpecValues::findAllBy('id', array_values($this->product_specs));
    }

    public function getSpecsAttribute()
    {
        return ProductSpecs::findAllBy('id', array_keys($this->product_specs));
    }

    public function image()
    {
        return $this->belongsTo(Config::get('media.file'), 'image_id');
    }

    public function getPriceAttribute($price)
    {
        return Money::create($price);
    }

    public function getNameAttribute()
    {
        return $this->product->name;
    }

    public function getUrlAttribute()
    {
        return $this->product->url;
    }

    public function getSkuId()
    {
        return $this->id;
    }

    /**
     * @return \Viviniko\Currency\Money
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return float
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->product_spec_names;
    }
}