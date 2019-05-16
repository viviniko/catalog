<?php

namespace Viviniko\Catalog\Observers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Viviniko\Catalog\Models\Attr;

class AttrObserver
{
    public function deleting(Attr $attr)
    {
        DB::table(Config::get('catalog.product_attr_table'))->where('attr_id', $attr->id)->delete();
    }
}