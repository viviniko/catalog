<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Facades\Config;
use Viviniko\Configuration\Configable;
use Viviniko\Support\Database\Eloquent\Model;
use Viviniko\Tag\CategoryTagTrait;
use Viviniko\Urlrewrite\UrlrewriteTrait;

class Category extends Model
{
    use UrlrewriteTrait, Configable, CategoryTagTrait;

    protected $tableConfigKey = 'catalog.categories_table';

    protected $fillable = [
        'name', 'description', 'banner', 'is_active', 'parent_id', 'path', 'picture_id', 'sort', 'attr_ids',
        'url_rewrite', 'meta_title', 'meta_keywords', 'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'attr_ids' => 'array',
    ];

    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(static::class, 'parent_id');
    }
}