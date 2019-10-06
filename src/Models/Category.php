<?php

namespace Viviniko\Catalog\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Viviniko\Catalog\Facades\Attrs;
use Viviniko\Configuration\Configable;
use Viviniko\Rewrite\RewriteTrait;
use Viviniko\Support\Database\Eloquent\Model;

class Category extends Model
{
    use RewriteTrait, Configable;

    protected $tableConfigKey = 'catalog.categories_table';

    protected $fillable = [
        'name', 'description', 'banner', 'is_active', 'parent_id', 'path', 'position', 'attr_ids',
        'slug', 'meta_title', 'meta_keywords', 'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'attr_ids' => 'array',
    ];

    public function getPathAttrIdsAttribute()
    {
        return Collection::make($this->path_ids)
            ->reduce(function (Collection $collect, $categoryId) {
                return $collect->merge($this->newQuery()->find($categoryId)->attr_ids);
            }, new Collection())->unique();
    }

    public function getPathIdsAttribute()
    {
        return explode('/', $this->path);
    }
    
    public function getPathCategoriesAttribute()
    {
        return $this->newQuery()->whereIn('id', $this->path_ids)->get();
    }

    public function parent()
    {
        return $this->belongsTo(Config::get('catalog.category'), 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Config::get('catalog.category'), 'parent_id');
    }

    public function getAttrsAttribute()
    {
        return Attrs::findAllBy('id', $this->attr_ids);
    }
}