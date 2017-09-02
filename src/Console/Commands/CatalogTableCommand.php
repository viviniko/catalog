<?php

namespace Viviniko\Catalog\Console\Commands;

use Viviniko\Support\Console\CreateMigrationCommand;

class CatalogTableCommand extends CreateMigrationCommand
{
    /**
     * @var string
     */
    protected $name = 'catalog:table';

    /**
     * @var string
     */
    protected $description = 'Create a migration for the catalog service table';

    /**
     * @var string
     */
    protected $stub = __DIR__.'/stubs/catalog.stub';

    /**
     * @var string
     */
    protected $migration = 'create_catalog_table';
}
