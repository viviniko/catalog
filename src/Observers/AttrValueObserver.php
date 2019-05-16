<?php

namespace Viviniko\Catalog\Observers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Viviniko\Catalog\Models\AttrValue;

class AttrValueObserver
{
    public function deleting(AttrValue $attrValue)
    {
        DB::table(Config::get('catalog.product_attr_table'))->where('attr_value_id', $attrValue->id)->delete();
    }
}