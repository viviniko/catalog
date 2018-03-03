<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Configuration\Configable;
use Viviniko\Support\Database\Eloquent\Model;
use Viviniko\Tag\TagCategoryTrait;
use Viviniko\Urlrewrite\UrlrewriteTrait;

class Category extends Model
{
    use UrlrewriteTrait, Configable, TagCategoryTrait;

    protected $tableConfigKey = 'catalog.categories_table';

    protected $fillable = [
        'name', 'description', 'is_active', 'parent_id', 'path', 'picture_id', 'sort',
        'url_rewrite', 'meta_title', 'meta_keywords', 'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function specificationGroups()
    {
        return $this->hasMany(Config::get('catalog.specification_group'), 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(static::class, 'parent_id');
    }
}