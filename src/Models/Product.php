<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Catalog\Services\Product\ProductCover;
use Viviniko\Favorite\Favoritable;
use Viviniko\Review\Reviewable;
use Viviniko\Support\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Laravel\Scout\Searchable;
use Viviniko\Tag\Taggable;
use Viviniko\Urlrewrite\UrlrewriteTrait;

class Product extends Model
{
    use Reviewable, Favoritable, UrlrewriteTrait, Taggable, Searchable {
        Searchable::searchable as makeSearchable;
    }

    protected $tableConfigKey = 'catalog.products_table';

    protected $fillable = [
        'category_id', 'name', 'spu', 'description', 'content', 'is_active', 'sort',
        'url_rewrite', 'meta_title', 'meta_keywords', 'meta_description',
        'created_by', 'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'content' => 'array',
    ];

    protected $appends = [
        'cover', 'sku', 'amount', 'currency', 'discount', 'quantity', 'weight'
    ];

    protected $hidden = [
        'created_by', 'updated_by', 'pictures', 'master',
    ];

    /**
     * @var \Viviniko\Catalog\Contracts\ProductService
     */
    protected static $productService;

    public function category()
    {
        return $this->belongsTo(Config::get('catalog.category'), 'category_id');
    }

    public function manufacturerProduct()
    {
        return $this->hasOne(Config::get('catalog.manufacturer_product'), 'product_id');
    }

    public function attrs()
    {
        return $this->belongsToMany(Config::get('catalog.attr'), Config::get('catalog.product_attr_table'));
    }

    public function specGroups()
    {
        return $this->belongsToMany(Config::get('catalog.spec_group'), Config::get('catalog.product_spec_group_table'))
            ->using(ProductSpecGroup::class)
            ->withPivot(['control_type', 'text_prompt', 'is_required', 'when', 'sort'])
            ->orderBy('pivot_sort');
    }

    public function specs()
    {
        return $this->belongsToMany(Config::get('catalog.spec'), Config::get('catalog.product_spec_table'))
            ->using(ProductSpec::class)
            ->withPivot(['customer_value', 'is_selected', 'picture_id', 'swatch_picture_id', 'sort'])
            ->orderBy('pivot_sort');
    }

    public function master()
    {
        return $this->hasOne(Config::get('catalog.item'), 'product_id')->where('is_master', true);
    }

    public function items()
    {
        return $this->hasMany(Config::get('catalog.item'), 'product_id');
    }

    public function pictures()
    {
        return $this->belongsToMany(Config::get('media.media'), Config::get('catalog.product_picture_table'), 'product_id', 'picture_id')
            ->withPivot(['sort'])
            ->orderBy('pivot_sort');
    }

    public function getUrlAttribute()
    {
        return url($this->url_rewrite);
    }

    public function getCoverAttribute()
    {
        if (!$this->product_cover) {
            $this->product_cover = new ProductCover($this->pictures->slice(0, 2)->map(function ($pic) { return $pic->url; }));
        }
        return $this->product_cover;
    }

    public function getSkuAttribute()
    {
        return data_get($this->master, 'sku');
    }

    public function getAmountAttribute()
    {
        return data_get($this->master, 'amount');
    }

    public function getCurrencyAttribute()
    {
        return data_get($this->master, 'currency');
    }

    public function getDiscountAttribute()
    {
        return data_get($this->master, 'discount');
    }

    public function getWeightAttribute()
    {
        return data_get($this->master, 'weight');
    }

    public function getQuantityAttribute()
    {
        return data_get($this->master, 'quantity');
    }

    public function getReviewableNameAttribute()
    {
        return $this->name;
    }

    /**
     * Make the given model instance searchable.
     *
     * @return void
     */
    public function searchable()
    {
        if (static::getProductService()->isProductCanSearchable($this->id)) {
            $this->makeSearchable();
        }
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'product';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return static::getProductService()->getProductSearchableArray($this->id);
    }

    /**
     * Get the field type mapping for the model.
     *
     * @return array
     */
    public function searchableMapping()
    {
        return static::getProductService()->getProductSearchableMapping();
    }

    public static function setProductService($productService)
    {
        static::$productService = $productService;
    }

    public static function getProductService()
    {
        if (!static::$productService) {
            static::$productService = app(\Viviniko\Catalog\Contracts\ProductService::class);
        }

        return static::$productService;
    }
}