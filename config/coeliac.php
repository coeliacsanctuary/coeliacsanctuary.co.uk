<?php

declare(strict_types=1);

return [
    'show_ads' => env('SHOW_ADS', true),

    'images_url' => env('IMAGES_URL'),

    'shop' => [
        'abandoned_basket_time_limit' => 2,

        'popular_products_window_days' => (int) env('SHOP_POPULAR_PRODUCTS_WINDOW_DAYS', 7),
    ],

    'generate_og_images' => (bool) env('GENERATE_OG_IMAGES', true),
    'generate_eatery_ai_descriptions' => (bool) env('GENERATE_EATERY_AI_DESCRIPTIONS', true),

    'cacheable' => [
        'blogs' => [
            'home' => 'cache.blogs.home',
            'tags' => 'cache.blogs.tags',
            'site-map' => 'cache.blogs.site-map',
        ],
        'recipes' => [
            'home' => 'cache.recipes.home',
            'site-map' => 'cache.recipes.site-map',
        ],
        'collections' => [
            'home' => 'cache.collections.home',
        ],
        'eating-out' => [
            'home' => 'cache.eating-out.home',
            'top-rated' => 'cache.eating-out.top-rated',
            'most-rated' => 'cache.eating-out.most-rated',
            'index-counts' => 'cache.eating-out.index-counts',
            'recently-added' => 'cache.eating-out.recently-added',
            'guide-statistics' => 'cache.eating-out.guide-statistics',
            'stats' => 'cache.eating-out.stats',
            'top-rated-in-county' => 'coeliac.eating-out.top-rated-in-county.{county.slug}',
            'most-rated-in-county' => 'coeliac.eating-out.most-rated-in-county.{county.slug}',
            'top-rated-in-town' => 'coeliac.eating-out.top-rated-in-county.{county.slug}.{town.slug}',
            'most-rated-in-town' => 'coeliac.eating-out.most-rated-in-county.{county.slug}.{town.slug}',
            'site-map-counties' => 'coeliac.eating-out.site-map.counties',
            'site-map-towns' => 'coeliac.eating-out.site-map.towns',
            'site-map-areas' => 'coeliac.eating-out.site-map.areas',
            'site-map-eateries' => 'coeliac.eating-out.site-map.eateries',
            'site-map-nationwide' => 'coeliac.eating-out.site-map.nationwide',
            'site-map-nationwide-branches' => 'coeliac.eating-out.site-map.nationwide-branches',
        ],
        'eating-out-reviews' => [
            'home' => 'cache.eating-out-reviews.home',
            'top-rated' => 'cache.eating-out.top-rated',
            'most-rated' => 'cache.eating-out.most-rated',
            'guide-statistics' => 'cache.eating-out.guide-statistics',
            'stats' => 'cache.eating-out.stats',
            'top-rated-in-county' => 'coeliac.eating-out.top-rated-in-county.{eatery.county.slug}',
            'most-rated-in-county' => 'coeliac.eating-out.most-rated-in-county.{eatery.county.slug}',
        ],
        'categories' => [
            'site-map' => 'coeliac.shop.categories.site-map',
        ],
        'products' => [
            'site-map' => 'coeliac.shop.products.site-map',
        ],
        'shop-reviews' => [
            'index' => 'coeliac.shop.reviews.index',
        ],
    ],
];
