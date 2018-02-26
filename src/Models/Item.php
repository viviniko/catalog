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
        return $this->belongsToMany(Config::get('catalog.attribute'), Config::get('catalog.item_attribute_table'));
    }

    public function getCoverAttribute()
    {
        return $this->picture->url;
    }
}