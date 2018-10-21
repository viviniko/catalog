<?php

namespace Viviniko\Catalog;

use Illuminate\Support\Facades\Event;
use Viviniko\Catalog\Console\Commands\CatalogTableCommand;
use Viviniko\Catalog\Listeners\CategoryEventSubscriber;
use Viviniko\Catalog\Listeners\ItemEventSubscriber;
use Viviniko\Catalog\Models\Category;
use Viviniko\Catalog\Models\Product;
use Viviniko\Catalog\Models\Item;
use Viviniko\Catalog\Observers\CategoryObserver;
use Viviniko\Catalog\Observers\MediaObserver;
use Viviniko\Catalog\Observers\ItemObserver;
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
        Item::observe(ItemObserver::class);
        Product::observe(ProductObserver::class);

        Event::subscribe(CategoryEventSubscriber::class);
        Event::subscribe(ItemEventSubscriber::class);

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

        $this->registerRepositories();

        $this->registerSKUGenerater();

        $this->registerCategoryService();

        $this->registerAttributeService();

        $this->registerSpecificationService();

        $this->registerProductService();

        $this->registerItemService();

        $this->registerManufacturerService();

        $this->registerCatalogService();

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

    public function registerRepositories()
    {
        // Category Repository
        $this->app->singleton(
            \Viviniko\Catalog\Repositories\Category\CategoryRepository::class,
            \Viviniko\Catalog\Repositories\Category\EloquentCategory::class
        );

        // Specification Group Repository
        $this->app->singleton(
            \Viviniko\Catalog\Repositories\SpecGroup\SpecGroupRepository::class,
            \Viviniko\Catalog\Repositories\SpecGroup\EloquentSpecGroup::class
        );

        // Specification Repository
        $this->app->singleton(
            \Viviniko\Catalog\Repositories\Spec\SpecRepository::class,
            \Viviniko\Catalog\Repositories\Spec\EloquentSpec::class
        );

        // Attribute Group Repository
        $this->app->singleton(
            \Viviniko\Catalog\Repositories\AttrGroup\AttrGroupRepository::class,
            \Viviniko\Catalog\Repositories\AttrGroup\EloquentAttrGroup::class
        );

        // Attribute Repository
        $this->app->singleton(
            \Viviniko\Catalog\Repositories\Attr\AttrRepository::class,
            \Viviniko\Catalog\Repositories\Attr\EloquentAttr::class
        );

        // Manufacturer Repository
        $this->app->singleton(
            \Viviniko\Catalog\Repositories\Manufacturer\ManufacturerRepository::class,
            \Viviniko\Catalog\Repositories\Manufacturer\EloquentManufacturer::class
        );

        // Product Repository
        $this->app->singleton(
            \Viviniko\Catalog\Repositories\Product\ProductRepository::class,
            \Viviniko\Catalog\Repositories\Product\EloquentProduct::class
        );

        // Item Repository
        $this->app->singleton(
            \Viviniko\Catalog\Repositories\Item\ItemRepository::class,
            \Viviniko\Catalog\Repositories\Item\EloquentItem::class
        );
    }

    /**
     * Register the category service provider.
     *
     * @return void
     */
    protected function registerCategoryService()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Services\CategoryService::class,
            \Viviniko\Catalog\Services\Impl\CategoryServiceImpl::class
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
            \Viviniko\Catalog\Services\AttrService::class,
            \Viviniko\Catalog\Services\Impl\AttrServiceImpl::class
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
            \Viviniko\Catalog\Services\SpecService::class,
            \Viviniko\Catalog\Services\Impl\SpecServiceImpl::class
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
            \Viviniko\Catalog\Services\ProductService::class,
            \Viviniko\Catalog\Services\Impl\ProductServiceImpl::class
        );
    }

    /**
     * Register the product item service provider.
     *
     * @return void
     */
    protected function registerItemService()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Services\ItemService::class,
            \Viviniko\Catalog\Services\Impl\ItemServiceImpl::class
        );
    }

    protected function registerManufacturerService()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Services\ManufacturerService::class,
            \Viviniko\Catalog\Services\Impl\ManufacturerServiceImpl::class
        );
    }

    protected function registerSKUGenerater()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Services\ProductSkuGenerater::class,
            \Viviniko\Catalog\Services\DefaultProductSkuGenerater::class
        );
    }

    protected function registerCatalogService()
    {
        $this->app->singleton('catalog', \Viviniko\Catalog\Catalog\CatalogManager::class);
        $this->app->alias('catalog',\Viviniko\Catalog\Contracts\Catalog::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'catalog',
            \Viviniko\Catalog\Contracts\Catalog::class,
            \Viviniko\Catalog\Services\CategoryService::class,
            \Viviniko\Catalog\Services\AttrService::class,
            \Viviniko\Catalog\Services\SpecService::class,
            \Viviniko\Catalog\Services\ProductService::class,
            \Viviniko\Catalog\Services\ItemService::class,
            \Viviniko\Catalog\Services\ManufacturerService::class,
        ];
    }
}