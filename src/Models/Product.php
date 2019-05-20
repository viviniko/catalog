<?php

namespace Viviniko\Catalog\Models;

use Laravel\Scout\Searchable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Viviniko\Catalog\ProductCover;
use Viviniko\Favorite\Facades\Favorites;
use Viviniko\Favorite\Favoritable;
use Viviniko\Media\Facades\Files;
use Viviniko\Review\Reviewable;
use Viviniko\Support\Database\Eloquent\Model;
use Viviniko\Tag\Taggable;
use Viviniko\Urlrewrite\UrlrewriteTrait;

class Product extends Model
{
    use Reviewable, Favoritable, UrlrewriteTrait, Taggable, Searchable {
        Searchable::searchable as makeSearchable;
    }

    protected $tableConfigKey = 'catalog.products_table';

    protected $fillable = [
        'category_id', 'name', 'spu', 'description', 'amount', 'picture_ids', 'detail', 'size_chart', 'is_active', 'sort',
        'url_rewrite', 'meta_title', 'meta_keywords', 'meta_description',
        'total_sold', 'month_sold', 'season_sold',
        'created_by', 'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'size_chart' => 'array',
        'picture_ids' => 'array',
    ];

    protected $appends = [
        'cover', 'sku', 'amount', 'discount', 'quantity', 'weight'
    ];

    protected $hidden = [
        'created_by', 'updated_by', 'master'
    ];

    public function category()
    {
        return $this->belongsTo(Config::get('catalog.category'), 'category_id');
    }

    public function manufacturerProduct()
    {
        return $this->hasOne(Config::get('catalog.manufacturer_product'), 'product_id');
    }

    public function attrValues()
    {
        return $this->belongsToMany(Config::get('catalog.attr_value'), Config::get('catalog.product_attr_table'));
    }

    public function specs()
    {
        return $this->belongsToMany(Config::get('catalog.spec'), Config::get('catalog.product_spec_table'))
            ->using(ProductSpec::class)
            ->withPivot(['control_type', 'text_prompt', 'is_required', 'sort'])
            ->orderBy('pivot_sort');
    }

    public function specValues()
    {
        return $this->belongsToMany(Config::get('catalog.spec_value'), Config::get('catalog.product_spec_value_table'))
            ->using(ProductSpecValue::class)
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

    public function getPicturesAttribute()
    {
        return Files::findAllBy('id', $this->picture_ids)->map(function ($media) { return $media->url; });
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
        $this->makeSearchable();
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
        $searchArray = $this->toArray();

        unset(
            $searchArray['master'],
            $searchArray['manufacturerProduct'],
            $searchArray['pictures'],
            $searchArray['cover'],
            $searchArray['url'],
            $searchArray['category']
        );

        if ($this->category) {
            $searchArray['category_name'] = $this->category->name;
            $searchArray['categories'] = $this->categoryService->getCategoriesByIdIn(array_filter(explode('/', $product->category->path)))->pluck('name')->implode(',');
        }

        $attrValueIds = [];
        $attrNames = [];
        $this->attrValues->each(function ($attrValue) use (&$attrValueIds, &$attrNames) {
            $attrValueIds[] = $attrValue->id;
            $attrNames[$attrValue->attr->name][] = $attrValue->name;
        });

        $latestMonthSold = max(1, $this->month_sold);
        $searchArray['month_sold'] = (int) $latestMonthSold;
        $searchArray['hot_score'] = (isset($searchArray['is_hot']) && $searchArray['is_hot'] ? 1 : 0) * 5 + $latestMonthSold;
        $searchArray['new_score'] = (isset($searchArray['is_new']) && $searchArray['is_new'] ? 1 : 0) * 5 + $latestMonthSold;
        $searchArray['promote_score'] = (isset($searchArray['is_promote']) && $searchArray['is_promote'] ? 1 : 0) * 5 + $latestMonthSold;
        $searchArray['recommend_score'] = $searchArray['hot_score'] * 3 + $searchArray['new_score'] * 2 + $searchArray['promote_score'] * 2;
        $searchArray['favorite_count'] = Favorites::count(['favoritable_type' => $this->getMorphClass(), 'favoritable_id' => $this->id]);

        $searchArray['amount'] = empty($searchArray['amount']) ? 0 : (float)$searchArray['amount']->value;
        $searchArray['sort'] = (int)$searchArray['sort'];

        $searchArray['attrs'] = $attrValueIds;
        foreach ($attrNames as $groupTitle => $specName) {
            $groupTitle = Str::slug($groupTitle, '_');
            $searchArray["attr_{$groupTitle}"] = implode(',', $specName);
        }

        $searchArray['created_at'] = (int) strtotime($product->created_at);
        $searchArray['updated_at'] = (int) strtotime($product->updated_at);

        return $searchArray;
    }

    /**
     * Get the field type mapping for the model.
     *
     * @return array
     */
    public function searchableMapping()
    {
        return [
            'properties' => [
                'price' => ['type' => 'float', 'coerce' => true],
                'market_price' => ['type' => 'float', 'coerce' => true],
                'weight' => ['type' => 'float', 'coerce' => true],
                'discount' => ['type' => 'float', 'coerce' => true],
                'created_at' => ['type' => 'long', 'coerce' => true],
                'updated_at' => ['type' => 'long', 'coerce' => true],
                'hot_score' => ['type' => 'long', 'coerce' => true],
                'new_score' => ['type' => 'long', 'coerce' => true],
                'promote_score' => ['type' => 'long', 'coerce' => true],
                'recommend_score' => ['type' => 'long', 'coerce' => true],
                'month_sold' => ['type' => 'long', 'coerce' => true],
                'favorite_count' => ['type' => 'long', 'coerce' => true],
                'sort' => ['type' => 'long', 'coerce' => true],
                'sku' => ['type' => 'keyword']
            ]
        ];
    }
}