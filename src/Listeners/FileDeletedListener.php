<?php

namespace Viviniko\Catalog\Listeners;

use Viviniko\Catalog\Services\ProductService;
use Viviniko\Media\Events\FileDeleted;

class FileDeletedListener
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function handle(FileDeleted $event)
    {
        $this->productService->detachProductPicture($event->file->id);
    }
}