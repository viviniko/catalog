<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class SpecValue extends Model
{
    protected $tableConfigKey = 'catalog.spec_values_table';

    protected $fillable = ['spec_id', 'name', 'slug', 'description', 'sort'];

    public function spec()
    {
        return $this->belongsTo(Config::get('catalog.spec'), 'spec_id');
    }
}