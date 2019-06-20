<?php

namespace Viviniko\Catalog;

use Illuminate\Support\Facades\Event;
use Viviniko\Catalog\Console\Commands\CatalogTableCommand;
use Viviniko\Catalog\Listeners\FileDeletedListener;
use Viviniko\Catalog\Models\Attr;
use Viviniko\Catalog\Models\AttrValue;
use Viviniko\Catalog\Models\Category;
use Viviniko\Catalog\Models\Product;
use Viviniko\Catalog\Models\Item;
use Viviniko\Catalog\Observers\AttrObserver;
use Viviniko\Catalog\Observers\AttrValueObserver;
use Viviniko\Catalog\Observers\CategoryObserver;
use Viviniko\Catalog\Observers\ItemObserver;
use Viviniko\Catalog\Observers\ProductObserver;
use Viviniko\Media\Events\FileDeleted;
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

        Category::observe(CategoryObserver::class);
        Item::observe(ItemObserver::class);
        Product::observe(ProductObserver::class);
        Attr::observe(AttrObserver::class);
        AttrValue::observe(AttrValueObserver::class);

        Event::listen(FileDeleted::class, FileDeletedListener::class);

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
            \Viviniko\Catalog\Repositories\SpecValue\SpecValueRepository::class,
            \Viviniko\Catalog\Repositories\SpecValue\EloquentSpecValue::class
        );

        // Specification Repository
        $this->app->singleton(
            \Viviniko\Catalog\Repositories\Spec\SpecRepository::class,
            \Viviniko\Catalog\Repositories\Spec\EloquentSpec::class
        );

        // Attribute Group Repository
        $this->app->singleton(
            \Viviniko\Catalog\Repositories\AttrValue\AttrValueRepository::class,
            \Viviniko\Catalog\Repositories\AttrValue\EloquentAttrValue::class
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

    protected function registerSKUGenerater()
    {
        $this->app->singleton(
            \Viviniko\Catalog\Services\ProductSkuGenerater::class,
            \Viviniko\Catalog\Services\DefaultProductSkuGenerater::class
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

        ];
    }
}