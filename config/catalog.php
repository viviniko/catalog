<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Catalog Category Model
    |--------------------------------------------------------------------------
    |
    | This is the Category model.
    |
    */
    'category' => 'Viviniko\Catalog\Models\Category',

    /*
    |--------------------------------------------------------------------------
    | Catalog Attribute Group Model
    |--------------------------------------------------------------------------
    |
    | This is the Attribute Group model.
    |
    */
    'attr_group' => 'Viviniko\Catalog\Models\AttrGroup',

    /*
    |--------------------------------------------------------------------------
    | Catalog Attribute Model
    |--------------------------------------------------------------------------
    |
    | This is the Attribute model.
    |
    */
    'attr' => 'Viviniko\Catalog\Models\Attr',

    /*
    |--------------------------------------------------------------------------
    | Catalog Specification Value Model
    |--------------------------------------------------------------------------
    |
    | This is the Specification Value model.
    |
    */
    'spec_group' => 'Viviniko\Catalog\Models\SpecGroup',

    /*
    |--------------------------------------------------------------------------
    | Catalog Specification Model
    |--------------------------------------------------------------------------
    |
    | This is the Specification model.
    |
    */
    'spec' => 'Viviniko\Catalog\Models\Spec',

    /*
    |--------------------------------------------------------------------------
    | Catalog Product Model
    |--------------------------------------------------------------------------
    |
    | This is the Product model.
    |
    */
    'product' => 'Viviniko\Catalog\Models\Product',

    /*
    |--------------------------------------------------------------------------
    | Catalog Product Item Model
    |--------------------------------------------------------------------------
    |
    | This is the Product Item model.
    |
    */
    'item' => 'Viviniko\Catalog\Models\Item',

    /*
    |--------------------------------------------------------------------------
    | Catalog Manufacturer Model
    |--------------------------------------------------------------------------
    |
    | This is the Manufacturer model.
    |
    */
    'manufacturer' => 'Viviniko\Catalog\Models\Manufacturer',

    /*
    |--------------------------------------------------------------------------
    | Catalog Product Manufacturer Model
    |--------------------------------------------------------------------------
    |
    | This is the product manufacturer model.
    |
    */
    'manufacturer_product' => 'Viviniko\Catalog\Models\ManufacturerProduct',

    /*
    |--------------------------------------------------------------------------
    | Catalog Categories Table
    |--------------------------------------------------------------------------
    |
    | This is the categories table.
    |
    */
    'categories_table' => 'catalog_categories',

    /*
    |--------------------------------------------------------------------------
    | Catalog Attributes Table
    |--------------------------------------------------------------------------
    |
    | This is the attributes table.
    |
    */
    'attrs_table' => 'catalog_attrs',

    /*
    |--------------------------------------------------------------------------
    | Catalog Attribute Values Table
    |--------------------------------------------------------------------------
    |
    | This is the attribute options table.
    |
    */
    'attr_groups_table' => 'catalog_attr_groups',

    /*
    |--------------------------------------------------------------------------
    | Catalog Specifications Table
    |--------------------------------------------------------------------------
    |
    | This is the specifications table.
    |
    */
    'specs_table' => 'catalog_specs',

    /*
    |--------------------------------------------------------------------------
    | Catalog Specification Groups Table
    |--------------------------------------------------------------------------
    |
    | This is the specification options table.
    |
    */
    'spec_groups_table' => 'catalog_spec_groups',

    /*
    |--------------------------------------------------------------------------
    | Catalog Manufacturers Table
    |--------------------------------------------------------------------------
    |
    | This is the manufacturers table.
    |
    */
    'manufacturers_table' => 'catalog_manufacturers',

    /*
    |--------------------------------------------------------------------------
    | Catalog Products Table
    |--------------------------------------------------------------------------
    |
    | This is the products table.
    |
    */
    'products_table' => 'catalog_products',

    /*
    |--------------------------------------------------------------------------
    | Catalog Product Items Table
    |--------------------------------------------------------------------------
    |
    | This is the product items table.
    |
    */
    'items_table' => 'catalog_product_items',

    /*
    |--------------------------------------------------------------------------
    | Catalog Product specification Mapping Table
    |--------------------------------------------------------------------------
    |
    | This is the product_specification table.
    |
    */
    'product_spec_table' => 'catalog_product_spec',

    /*
    |--------------------------------------------------------------------------
    | Catalog Product Specification Group Mapping Table
    |--------------------------------------------------------------------------
    |
    | This is the product_attribute_group_table table.
    |
    */
    'product_spec_group_table' => 'catalog_product_spec_group',

    /*
    |--------------------------------------------------------------------------
    | Catalog Product Attribute Mapping Table
    |--------------------------------------------------------------------------
    |
    | This is the product_attribute table.
    |
    */
    'product_attr_table' => 'catalog_product_attr',

    /*
    |--------------------------------------------------------------------------
    | Catalog Product Item Specification Mapping Table
    |--------------------------------------------------------------------------
    |
    | This is the item_specification_table table.
    |
    */
    'item_spec_table' => 'catalog_product_item_spec',

    /*
    |--------------------------------------------------------------------------
    | Catalog Product Picture Table
    |--------------------------------------------------------------------------
    |
    | This is the product_picture table.
    |
    */
    'product_picture_table' => 'catalog_product_picture',

    /*
    |--------------------------------------------------------------------------
    | Catalog Product Manufacturer Table
    |--------------------------------------------------------------------------
    |
    | This is the product_manufacturer table.
    |
    */
    'manufacturer_products_table' => 'catalog_manufacturer_products',

];