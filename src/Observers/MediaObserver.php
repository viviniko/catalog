<?php

namespace Viviniko\Catalog\Observers;

use Viviniko\Catalog\Services\ProductService;
use Viviniko\Media\Models\Media;

class MediaObserver
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function deleted(Media $media)
    {
        $this->productService->detachProductPicture($media->id);
    }
}