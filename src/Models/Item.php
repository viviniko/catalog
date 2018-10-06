<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Currency\Amount;
use Viviniko\Support\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Item extends Model
{
    public $timestamps = false;

    protected $tableConfigKey = 'catalog.items_table';

    protected $fillable = [
        'product_id', 'sku', 'amount', 'currency', 'discount', 'weight', 'quantity', 'picture_id', 'is_active', 'is_master'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_master' => 'boolean',
    ];

    protected $hidden = [
        'is_master', 'weight', 'picture_id', 'is_active'
    ];

    protected $appends = [
        'price', 'discount_price'
    ];

    public function product()
    {
        return $this->belongsTo(Config::get('catalog.product'), 'product_id');
    }

    public function picture()
    {
        return $this->belongsTo(Config::get('media.media'), 'picture_id');
    }

    public function specs()
    {
        return $this->belongsToMany(Config::get('catalog.spec'), Config::get('catalog.item_spec_table'))->select(Config::get('catalog.specs_table'). '.*');
    }

    public function getDescSpecsAttribute()
    {
        return $this->specs->pluck('value', 'group.text_prompt')->toArray();
    }

    public function getSkuKeyAttribute()
    {
        return $this->specs()->pluck('id')->sort()->implode(':');
    }

    public function getCoverAttribute()
    {
        return data_get($this->picture, 'url');
    }

    public function getPriceAttribute()
    {
        if (!$this->_price) {
            $this->_price = new Amount($this->currency, $this->amount);
        }
        return $this->_price;
    }

    public function getDiscountPriceAttribute()
    {
        if (!$this->_discount_price) {
            $this->_discount_price = new Amount($this->currency, $this->amount, $this->discount);
        }
        return $this->_discount_price;
    }
}