<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Support\Database\Eloquent\Model;

class Spec extends Model
{
    protected $tableConfigKey = 'catalog.specs_table';

    protected $fillable = ['group_id', 'name', 'description', 'sort'];

    public function group()
    {
        return $this->belongsTo(Config::get('catalog.spec_group'), 'group_id');
    }
}