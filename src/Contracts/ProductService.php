<?php

namespace Viviniko\Catalog\Contracts;

interface ProductService
{
    /**
     * Search products.
     *
     * @param null $keyword
     * @param null $filters
     * @param null $order
     * @param null $except
     * @param array $categories
     * @return \Laravel\Scout\Builder
     */
    public function search($keyword = null, $filters = null, $order = null, $except = null, $categories = []);

    /**
     * Find data by id
     *
     * @param       $id
     *
     * @return mixed
     */
    public function find($id);

    /**
     * Save a new entity in repository
     *
     * @param array $data
     *
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update a entity in repository by id
     *
     * @param       $id
     * @param array $data
     *
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Delete a entity in repository by id
     *
     * @param $id
     *
     * @return int
     */
    public function delete($id);

    /**
     * Attach attribute groups.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function attachAttributeGroups($productId, array $data);

    /**
     * Update attribute group.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function updateAttributeGroups($productId, array $data);

    /**
     * Detach attribute group.
     *
     * @param $productId
     * @param $specificationGroupId
     *
     * @return mixed
     */
    public function detachAttributeGroup($productId, $specificationGroupId);

    /**
     * Attach attribute.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function attachAttribute($productId, array $data);

    /**
     * Update attribute.
     *
     * @param $productId
     * @param array $data
     *
     * @return mixed
     */
    public function updateAttribute($productId, array $data);

    /**
     * Detach attribute.
     *
     * @param $productId
     * @param $specificationId
     *
     * @return mixed
     */
    public function detachAttribute($productId, $specificationId);

    /**
     * Detach product picture.
     *
     * @param $pictureId
     * @return mixed
     */
    public function detachProductPicture($pictureId);

    /**
     * @param array $attributes
     * @param null $x
     * @param null $y
     * @return mixed
     */
    public function addProductAttributeSwatchPicture(array &$attributes, $x = null, $y = null);

    /**
     * @param $productId
     * @param $attributeId
     * @param $pictureId
     * @param $x
     * @param $y
     * @return mixed
     */
    public function updateProductAttributeSwatchPicture($productId, $attributeId, $pictureId, $x, $y);

    /**
     * @param $productId
     * @return mixed
     */
    public function getProductSearchableMapping();

    /**
     * @param $productId
     * @return mixed
     */
    public function getProductSearchableArray($productId);

    /**
     * @param $productId
     * @return bool
     */
    public function isProductCanSearchable($productId);

    /**
     * @param $productId
     * @return mixed
     */
    public function getProductSwatchPictures($productId);

    /**
     * @return mixed
     */
    public function generateProductItems($productId);
}