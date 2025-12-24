<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shop Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration values for the shop functionality
    | including pagination, limits, and business rules.
    |
    */

    'pagination' => [
        'products_per_page' => env('SHOP_PRODUCTS_PER_PAGE', 12),
        'reviews_per_product' => env('SHOP_REVIEWS_PER_PRODUCT', 10),
        'related_products_limit' => env('SHOP_RELATED_PRODUCTS_LIMIT', 4),
        'reviews_per_page' => env('SHOP_REVIEWS_PER_PAGE', 10),
        'orders_per_page' => env('SHOP_ORDERS_PER_PAGE', 15),
        'seller_products_per_page' => env('SELLER_PRODUCTS_PER_PAGE', 15),
    ],

    'cart' => [
        'max_quantity_per_item' => env('CART_MAX_QUANTITY_PER_ITEM', 100),
        'min_quantity_per_item' => env('CART_MIN_QUANTITY_PER_ITEM', 1),
        'session_key' => 'cart',
    ],

    'product' => [
        'min_price' => env('PRODUCT_MIN_PRICE', 0.01),
        'max_price' => env('PRODUCT_MAX_PRICE', 99999.99),
        'max_name_length' => env('PRODUCT_MAX_NAME_LENGTH', 255),
        'max_description_length' => env('PRODUCT_MAX_DESCRIPTION_LENGTH', 5000),
    ],

    'review' => [
        'min_rating' => 1,
        'max_rating' => 5,
        'max_comment_length' => env('REVIEW_MAX_COMMENT_LENGTH', 1000),
    ],

    'file_upload' => [
        'max_size_mb' => env('FILE_UPLOAD_MAX_SIZE_MB', 10),
        'max_size_kb' => env('FILE_UPLOAD_MAX_SIZE_KB', 10240),
        'allowed_extensions' => ['csv', 'txt', 'json', 'xlsx', 'xls'],
        'allowed_mimes' => 'csv,txt,json,xlsx,xls',
        'batch_size' => env('FILE_UPLOAD_BATCH_SIZE', 500),
    ],

    'order' => [
        'number_prefix' => 'ORD-',
        'number_random_length' => 10,
    ],

    'sorting' => [
        'options' => [
            'newest' => ['column' => 'created_at', 'direction' => 'desc'],
            'price_asc' => ['column' => 'price', 'direction' => 'asc'],
            'price_desc' => ['column' => 'price', 'direction' => 'desc'],
            'name' => ['column' => 'name', 'direction' => 'asc'],
        ],
        'default' => 'newest',
    ],

    'product_status' => [
        'draft' => 'draft',
        'active' => 'active',
        'inactive' => 'inactive',
    ],

    'order_status' => [
        'pending' => 'pending',
        'processing' => 'processing',
        'completed' => 'completed',
        'cancelled' => 'cancelled',
        'refunded' => 'refunded',
    ],

    'payment_status' => [
        'pending' => 'pending',
        'paid' => 'paid',
        'failed' => 'failed',
        'refunded' => 'refunded',
    ],

    'product_item_status' => [
        'available' => 'available',
        'sold' => 'sold',
        'reserved' => 'reserved',
    ],
];
