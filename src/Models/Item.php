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
        'product_id', 'vs', 'values', 'sku', 'amount', 'discount', 'weight', 'quantity', 'picture_id',
        'is_active', 'is_master'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_master' => 'boolean',
        'values' => 'array',
    ];

    protected $hidden = [
        'is_master', 'weight', 'picture_id', 'is_active'
    ];

    public function product()
    {
        return $this->belongsTo(Config::get('catalog.product'), 'product_id');
    }

    public function picture()
    {
        return $this->belongsTo(Config::get('media.file'), 'picture_id');
    }

    public function getCoverAttribute()
    {
        return data_get($this->picture, 'url');
    }

    public function getAmountAttribute($amount)
    {
        return Amount::createBaseAmount($amount);
    }
}