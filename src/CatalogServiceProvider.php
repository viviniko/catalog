<?php

namespace Viviniko\Catalog;

use Viviniko\Catalog\Console\Commands\CatalogTableCommand;
use Viviniko\Catalog\Models\Category;
use Viviniko\Catalog\Models\Product;
use Viviniko\Catalog\Models\ProductItem;
use Viviniko\Catalog\Observers\CategoryObserver;
use Viviniko\Catalog\Observers\MediaObserver;
use Viviniko\Catalog\Observers\ProductItemObserver;
use Viviniko\Catalog\Observers\ProductObserver;
use Viviniko\Media\Models\Media;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;


class CatalogServiceProvider extends BaseServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
            __DIR__.'/../config/catalog.php' => config_path('catalog.php'),
        ]);

        // Register commands
        $this->commands('command.catalog.table');

        Media::observe(MediaObserver::class);
        Category::observe(CategoryObserver::class);
        ProductItem::observe(ProductItemObserver::class);
        Product::observe(ProductObserver::class);

        $config = $this->app['config'];

        Relation::morphMap([
            'catalog.category' => $config->get('catalog.category'),
            'catalog.product' => $config->get('catalog.product'),
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/catalog.php', 'catalog');

        $this->registerCategoryService();

        $this->registerAttributeService();

        $this->registerAttributeGroupService();

        $this->registerSpecificationService();

        $this->registerSpecificationGroupService();

        $this->registerManufacturerService();

        $this->registerProductService();

        $this->registerProductItemService();

        $this->registerCommands();
    }

    /**
     * Register the artisan commands.
     *
     * @return void
     */
    private function registerCommands()
    {
        $this->app->singleton('command.catalog.table', function ($app) {
            return new CatalogTableCommand($app['files'], $app['composer']);
        });
    }

    /**
     * Register the category service provider.
     *
     * @return void
     */
    protected function registerCategoryService()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Contracts\CategoryService::class,
            \Viviniko\Catalog\Services\Category\EloquentCategory::class
        );
    }

    /**
     * Register the attribute service provider.
     *
     * @return void
     */
    protected function registerAttributeService()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Contracts\AttributeService::class,
            \Viviniko\Catalog\Services\Attribute\EloquentAttribute::class
        );
    }

    /**
     * Register the attribute service provider.
     *
     * @return void
     */
    protected function registerAttributeGroupService()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Contracts\AttributeGroupService::class,
            \Viviniko\Catalog\Services\AttributeGroup\EloquentAttributeGroup::class
        );
    }

    /**
     * Register the specification attribute service provider.
     *
     * @return void
     */
    protected function registerSpecificationService()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Contracts\SpecificationService::class,
            \Viviniko\Catalog\Services\Specification\EloquentSpecification::class
        );
    }

    /**
     * Register the specification value service provider.
     *
     * @return void
     */
    protected function registerSpecificationGroupService()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Contracts\SpecificationGroupService::class,
            \Viviniko\Catalog\Services\SpecificationGroup\EloquentSpecificationGroup::class
        );
    }

    /**
     * Register the product service provider.
     *
     * @return void
     */
    protected function registerProductService()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Contracts\ProductService::class,
            \Viviniko\Catalog\Services\Product\EloquentProduct::class
        );
    }

    /**
     * Register the product item service provider.
     *
     * @return void
     */
    protected function registerProductItemService()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Contracts\ProductItemService::class,
            \Viviniko\Catalog\Services\ProductItem\EloquentProductItem::class
        );
    }

    /**
     * Register the manufacturer service provider.
     *
     * @return void
     */
    protected function registerManufacturerService()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Contracts\ManufacturerService::class,
            \Viviniko\Catalog\Services\Manufacturer\EloquentManufacturer::class
        );
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            \Viviniko\Catalog\Contracts\CategoryService::class,
            \Viviniko\Catalog\Contracts\AttributeService::class,
            \Viviniko\Catalog\Contracts\AttributeGroupService::class,
            \Viviniko\Catalog\Contracts\SpecificationService::class,
            \Viviniko\Catalog\Contracts\SpecificationGroupService::class,
            \Viviniko\Catalog\Contracts\ProductService::class,
            \Viviniko\Catalog\Contracts\ProductItemService::class,
            \Viviniko\Catalog\Contracts\ManufacturerService::class,
        ];
    }
}