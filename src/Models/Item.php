<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Cart\Contracts\CartItem;
use Viviniko\Catalog\Facades\ProductSpecs;
use Viviniko\Catalog\Facades\ProductSpecValues;
use Viviniko\Currency\Amount;
use Viviniko\Support\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Item extends Model implements CartItem
{
    public $timestamps = false;

    protected $tableConfigKey = 'catalog.items_table';

    protected $fillable = [
        'product_id', 'product_specs', 'product_spec_names', 'sku', 'amount', 'discount', 'weight', 'quantity', 'picture_id',
        'is_active', 'is_primary'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
        'product_specs' => 'array',
        'product_spec_names' => 'array',
    ];

    protected $hidden = [
        'is_master', 'weight', 'picture_id', 'is_active'
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

    public function picture()
    {
        return $this->belongsTo(Config::get('media.file'), 'picture_id');
    }

    public function getImageAttribute()
    {
        return data_get($this->picture, 'url');
    }

    public function getAmountAttribute($amount)
    {
        return Amount::createBaseAmount($amount);
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
     * @return \Viviniko\Currency\Amount
     */
    public function getPrice()
    {
        return $this->amount;
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