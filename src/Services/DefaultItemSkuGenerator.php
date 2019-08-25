<?php

namespace Viviniko\Catalog\Services;

use Viviniko\Catalog\Repositories\Category\CategoryRepository;
use Viviniko\Catalog\Repositories\Item\ItemRepository;

class DefaultItemSkuGenerator implements ItemSkuGenerator
{
    protected $items;

    protected $categories;

    public function __construct(ItemRepository $items, CategoryRepository $categories)
    {
        $this->items = $items;
        $this->categories = $categories;
    }

    public function generate($itemId, array $options = [])
    {
        $item = $this->items->find($itemId);
        $category = $item->product->category;

        $cids = array_filter(explode('/', $category->path_ids));
        $prefix = '';
        if (count($cids) == 1) {
            $category = $this->categories->find($cids[0]);
            $prefix = substr($category->name, 0, 3);
        } else {
            foreach (array_slice($cids, 0, 2) as $i => $cid) {
                $category = $this->categories->find($cid);
                if ($i == 0) {
                    $prefix .= $category->name[0];
                } else {
                    $words = explode(' ', $category->name);
                    if (count($words) == 1) {
                        $prefix .= $category->name[0] . $category->name[1];
                    } else {
                        $prefix .= $words[0][0] . $words[1][0];
                    }
                }
            }
        }
        foreach ($item->product_spec_names as $specValueName) {
            $prefix .= $specValueName[0];
        }

        return strtoupper(($options['prefix'] ?? '') . $prefix . $item->id . ($options['suffix'] ?? ''));
    }
}