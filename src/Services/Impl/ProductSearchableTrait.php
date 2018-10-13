<?php

namespace Viviniko\Catalog\Services\Impl;

trait ProductSearchableTrait
{
    /**
     * @return mixed
     */
    public function getProductSearchableMapping()
    {
        return [
            'properties' => [
                'price' => ['type' => 'float', 'coerce' => true],
                'market_price' => ['type' => 'float', 'coerce' => true],
                'weight' => ['type' => 'float', 'coerce' => true],
                'created_at' => ['type' => 'long', 'coerce' => true],
                'updated_at' => ['type' => 'long', 'coerce' => true],
                'hot_score' => ['type' => 'long', 'coerce' => true],
                'new_score' => ['type' => 'long', 'coerce' => true],
                'promote_score' => ['type' => 'long', 'coerce' => true],
                'recommend_score' => ['type' => 'long', 'coerce' => true],
                'quarter_sold_count' => ['type' => 'long', 'coerce' => true],
                'favorite_count' => ['type' => 'long', 'coerce' => true],
                'sort' => ['type' => 'long', 'coerce' => true],
                'sku' => ['type' => 'keyword']
            ]
        ];
    }

    /**
     * @param $productId
     * @return mixed
     */
    public function getProductSearchableArray($productId)
    {
        $product = $this->productRepository->find($productId);

        $searchArray = $product->toArray();

        unset(
            $searchArray['master'],
            $searchArray['manufacturerProduct'],
            $searchArray['pictures'],
            $searchArray['cover'],
            $searchArray['url'],
            $searchArray['category']
        );

        if ($product->category) {
            $searchArray['category_name'] = $product->category->name;
            $searchArray['categories'] = $this->categoryService->find(array_filter(explode('/', $product->category->path)))->pluck('name')->implode(',');
        }

        $attrIds = [];
        $attrNames = [];
        $this->getAttrService()->getSearchableAttrsByProductId($productId)->each(function ($attr) use (&$attrIds, &$attrNames) {
            $attrIds[] = $attr->id;
            $attrNames[$attr->group->title][] = $attr->title;
        });

        $orderService = $this->getOrderService();
        $favoriteService = $this->getFavoriteService();
        $latestQuarterSold = $orderService ? $orderService->countOrderProductQtyByLatestMonth($product->id, 3) : 1;
        $searchArray['quarter_sold_count'] = (int) $latestQuarterSold;
        $searchArray['hot_score'] = (isset($searchArray['is_hot']) && $searchArray['is_hot'] ? 1 : 0) * 5 + $latestQuarterSold;
        $searchArray['new_score'] = (isset($searchArray['is_new']) && $searchArray['is_new'] ? 1 : 0) * 5 + $latestQuarterSold;
        $searchArray['promote_score'] = (isset($searchArray['is_promote']) && $searchArray['is_promote'] ? 1 : 0) * 5 + $latestQuarterSold;
        $searchArray['recommend_score'] = $searchArray['hot_score'] * 3 + $searchArray['new_score'] * 2 + $searchArray['promote_score'] * 2;
        $searchArray['favorite_count'] = $favoriteService ? $favoriteService->count($product) : 0;

        $searchArray['amount'] = (float)$searchArray['amount'];
        $searchArray['weight'] = (float)$searchArray['weight'];
        $searchArray['sort'] = (int)$searchArray['sort'];

        $searchArray['attrs'] = $attrIds;
        foreach ($attrNames as $groupTitle => $specName) {
            $groupTitle = str_slug($groupTitle, '_');
            $searchArray["attr_{$groupTitle}"] = implode(',', $specName);
        }

        $searchArray['created_at'] = (int) strtotime($product->created_at);
        $searchArray['updated_at'] = (int) strtotime($product->updated_at);

        return $searchArray;
    }

    /**
     * @return \Viviniko\Favorite\Contracts\FavoriteService
     */
    private function getFavoriteService()
    {
        return app(\Viviniko\Favorite\Contracts\FavoriteService::class);
    }

    /**
     * @return \Viviniko\Sale\Services\OrderService
     */
    private function getOrderService()
    {
        return app(\Viviniko\Sale\Services\OrderService::class);
    }

    /**
     * @return \Viviniko\Catalog\Services\AttrService
     */
    private function getAttrService()
    {
        return app(\Viviniko\Catalog\Services\AttrService::class);
    }

    /**
     * @param $productId
     * @return bool
     */
    public function isProductCanSearchable($productId)
    {
        return true;
    }
}