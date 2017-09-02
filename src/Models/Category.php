<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Support\Database\Eloquent\Model;
use Viviniko\Urlrewrite\UrlrewriteTrait;

class Category extends Model
{
    use UrlrewriteTrait;

    protected $tableConfigKey = 'catalog.categories_table';

    protected $fillable = [
        'name', 'description', 'is_active', 'parent_id', 'path', 'picture_id', 'sort',
        'meta_title', 'meta_keywords', 'meta_description', 'url_rewrite',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function specificationGroups()
    {
        return $this->hasMany(SpecificationGroup::class, 'category_id');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
}