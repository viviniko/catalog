<?php

namespace Viviniko\Catalog\Models;

use Viviniko\Favorite\Favoritable;
use Viviniko\Media\Models\Media;
use Viviniko\Review\Reviewable;
use Viviniko\Support\Database\Eloquent\Model;
use Viviniko\Urlrewrite\UrlrewriteTrait;
use Illuminate\Support\Facades\Config;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Reviewable, UrlrewriteTrait, Favoritable, Searchable;

    protected $tableConfigKey = 'catalog.products_table';

    protected $fillable = [
        'category_id',
        'name', 'description', 'content', 'url_rewrite', 'meta_title', 'meta_keywords', 'meta_description',
        'is_active', 'is_new', 'is_hot', 'is_promote', 'sort', 'created_by', 'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_hot' => 'boolean',
        'is_new' => 'boolean',
        'is_promote' => 'boolean',
        'content' => 'array',
    ];

    protected $appends = [
        'sku_id', 'url', 'cover', 'sku', 'market_price', 'price', 'stock_quantity', 'upc', 'weight'
    ];

    protected $hidden = [
        'created_by', 'updated_by', 'pictures', 'master', 'url_rewrite'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function manufacturer()
    {
        return $this->hasOne(ProductManufacturer::class, 'product_id');
    }

    public function specifications()
    {
        return $this->belongsToMany(Specification::class, Config::get('catalog.product_specification_table'));
    }

    public function attributeGroups()
    {
        return $this->belongsToMany(AttributeGroup::class, Config::get('catalog.product_attribute_group_table'))
            ->using(ProductAttributeGroup::class)
            ->withPivot(['control_type', 'text_prompt', 'is_required', 'when', 'sort'])->orderBy('sort');
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, Config::get('catalog.product_attribute_table'))
            ->using(ProductAttribute::class)
            ->withPivot(['customer_value', 'is_selected', 'picture_id', 'sort']);
    }

    public function master()
    {
        return $this->hasOne(ProductItem::class, 'product_id')->where('is_master', true);
    }

    public function items()
    {
        return $this->hasMany(ProductItem::class, 'product_id');
    }

    public function pictures()
    {
        return $this->belongsToMany(Media::class, Config::get('catalog.product_picture_table'), 'product_id', 'picture_id')
            ->withPivot(['sort'])->orderBy('sort');
    }

    public function getUrlAttribute()
    {
        return url($this->url_rewrite);
    }

    public function getCoverAttribute()
    {
        return data_get($this->pictures->sortBy('sort')->first(), 'url');
    }

    public function getSkuIdAttribute()
    {
        return data_get($this->master, 'id');
    }

    public function getSkuAttribute()
    {
        return data_get($this->master, 'sku');
    }

    public function getMarketPriceAttribute()
    {
        return data_get($this->master, 'market_price');
    }

    public function getPriceAttribute()
    {
        return data_get($this->master, 'price');
    }

    public function getUpcAttribute()
    {
        return data_get($this->master, 'upc');
    }

    public function getWeightAttribute()
    {
        return data_get($this->master, 'weight');
    }

    public function getStockQuantityAttribute()
    {
        return data_get($this->master, 'stock_quantity');
    }

    public function getReviewableNameAttribute()
    {
        return $this->name;
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

        unset($searchArray['master'], $searchArray['pictures']);

        if ($this->category) {
            $searchArray['category_name'] = $this->category->name;
            $searchArray['categories'] = app(\Common\Catalog\Contracts\CategoryService::class)
                ->getAllChildren($this->category_id)
                ->pluck('name')
                ->prepend($this->category->name)
                ->implode(',');
        }

        $specIds = [];
        $specNames = [];
        app(\Common\Catalog\Contracts\ProductService::class)->getSpecifications($this->id)->each(function ($spec) use (&$specIds, &$specNames) {
            $specIds[] = $spec->id;
            if (data_get($spec->group, 'is_searchable')) {
                $specNames[] = $spec->name;
            }
        });

        $searchArray['price'] = (float)$searchArray['price'];
        $searchArray['market_price'] = (float)$searchArray['market_price'];
        $searchArray['weight'] = (float)$searchArray['weight'];
        $searchArray['sort'] = (int)$searchArray['sort'];

        $searchArray['specifications'] = $specIds;
        $searchArray['specification_names'] = $specNames;
        $searchArray['created_at'] = strtotime($this->created_at);
        $searchArray['updated_at'] = strtotime($this->updated_at);

        return $searchArray;
    }

    public function searchableMapping()
    {
        return [
            'properties' => [
                'price' => ['type' => 'double', 'coerce' => true],
                'market_price' => ['type' => 'double', 'coerce' => true],
                'weight' => ['type' => 'double', 'coerce' => true],
                'created_at' => ['type' => 'long', 'coerce' => true],
                'updated_at' => ['type' => 'long', 'coerce' => true],
                'sort' => ['type' => 'long', 'coerce' => true],
            ]
        ];
    }
}