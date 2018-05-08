<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Support\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

class Item extends Model
{
    public $timestamps = false;

    protected $tableConfigKey = 'catalog.items_table';

    protected $fillable = [
        'product_id', 'sku', 'market_price', 'price', 'weight', 'quantity', 'picture_id', 'is_active', 'is_master'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_master' => 'boolean',
    ];

    protected $hidden = [
        'is_master', 'weight', 'picture_id', 'is_active',
    ];

    public function product()
    {
        return $this->belongsTo(Config::get('catalog.product'), 'product_id');
    }

    public function picture()
    {
        return $this->belongsTo(Config::get('media.media'), 'picture_id');
    }

    public function attrs()
    {
        return $this->belongsToMany(Config::get('catalog.attribute'), Config::get('catalog.item_attribute_table'))->select(Config::get('catalog.attributes_table'). '.*');
    }

    public function getDescAttrsAttribute()
    {
        return $this->attrs->pluck('value', 'group.text_prompt')->toArray();
    }

    public function getSkuKeyAttribute()
    {
        return $this->attrs()->pluck('id')->sort()->implode(':');
    }

    public function getCoverAttribute()
    {
        return data_get($this->picture, 'url');
    }
}