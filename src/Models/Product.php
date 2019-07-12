<?php

namespace Viviniko\Catalog\Models;

use Laravel\Scout\Searchable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
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
        'sku', 'amount', 'discount', 'quantity', 'weight'
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

    public function attrs()
    {
        return $this->hasMany(Config::get('catalog.product_attr'), 'product_id');
    }

    public function specs()
    {
        return $this->hasMany(Config::get('catalog.product_spec'), 'product_id');
    }

    public function primary()
    {
        return $this->hasOne(Config::get('catalog.item'), 'product_id')->where('is_primary', true);
    }

    public function items()
    {
        return $this->hasMany(Config::get('catalog.item'), 'product_id');
    }

    public function getPicturesAttribute()
    {
        return Files::findAllBy('id', $this->picture_ids);
    }

    public function getUrlAttribute()
    {
        return url($this->url_rewrite);
    }

    public function getPictureAttribute()
    {
        return data_get($this->primary, 'picture');
    }

    public function getSkuAttribute()
    {
        return data_get($this->primary, 'sku');
    }

    public function getAmountAttribute()
    {
        return data_get($this->primary, 'amount');
    }

    public function getDiscountAttribute()
    {
        return data_get($this->primary, 'discount');
    }

    public function getWeightAttribute()
    {
        return data_get($this->primary, 'weight');
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
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $searchArray = $this->toArray();

        unset(
            $searchArray['primary'],
            $searchArray['manufacturerProduct'],
            $searchArray['pictures'],
            $searchArray['picture'],
            $searchArray['url'],
            $searchArray['url_rewrite'],
            $searchArray['picture_ids'],
            $searchArray['category'],
            $searchArray['size_chart']
        );

        if ($this->category) {
            // $searchArray['category_name'] = $this->category->name;
            $searchArray['categories'] = $this->category->path_categories->pluck('name')->implode(',');
        }

        $attrValueIds = [];
        $attrNames = [];
        $this->attrs->each(function ($attr) use (&$attrValueIds, &$attrNames) {
            foreach ($attr->values as $value) {
                $attrValueIds[] = $value->attr_value_id;
                $attrNames[$attr->name][] = $value->name;
            }

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

        foreach ($attrNames as $groupTitle => $specName) {
            $groupTitle = Str::slug($groupTitle, '_');
            $searchArray["attr_{$groupTitle}"] = implode(',', $specName);
        }

        $searchArray['created_at'] = (int) strtotime($this->created_at);
        $searchArray['updated_at'] = (int) strtotime($this->updated_at);

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
                'amount' => ['type' => 'float'],
                'weight' => ['type' => 'float'],
                'discount' => ['type' => 'float'],
                'created_at' => ['type' => 'long'],
                'updated_at' => ['type' => 'long'],
                'hot_score' => ['type' => 'long'],
                'new_score' => ['type' => 'long'],
                'promote_score' => ['type' => 'long'],
                'recommend_score' => ['type' => 'long'],
                'month_sold' => ['type' => 'long'],
                'favorite_count' => ['type' => 'long'],
                'sort' => ['type' => 'long'],
                'sku' => ['type' => 'keyword']
            ]
        ];
    }
}