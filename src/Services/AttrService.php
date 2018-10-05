<?php

namespace Viviniko\Catalog\Services;

interface AttrService
{
    public function getGroupsInCategoryId($categoryId);

    public function createGroup(array $data);

    public function updateGroup($attrGroupId, array $data);

    public function deleteGroup($attrGroupId);

    public function createAttr(array $data);

    public function updateAttr($attrId, array $data);

    public function deleteAttr($attrId);
    
    public function find($id);

    /**
     * Get specification attributes by category id.
     *
     * @param $categoryId
     * @return mixed
     */
    public function getFilterableAttrsByCategoryId($categoryId);

    /**
     * Get specification attributes by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getSearchableAttrsByProductId($productId);

    /**
     * Get specification attributes by product id.
     *
     * @param $productId
     * @return mixed
     */
    public function getViewableAttrsByProductId($productId);
}