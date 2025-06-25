<?php

declare(strict_types=1);

return [
    'images_url' => env('IMAGES_URL'),

    'shop' => [
        'abandoned_basket_time_limit' => 2,

        'product_postage_description' => <<<'TEXT'
            <ul>
                <li>Orders are only processed on normal UK working days.</li>
                <li>
                    <strong>UK</strong>
                    <ul>
                        <li>All orders are sent by Royal Mail First Class Post</li>
                        <li>All orders usually dispatched within 2 working days.</li>
                        <li>You will receive an email when your order has been dispatched.</li>
                        <li>Royal Mail state that 90% of orders will be delivered next working day.</li>
                    </ul>
                </li>
                <li>
                    <strong>Rest of the world</strong>
                    <ul>
                        <li>All orders are sent by Royal Mail International Standard Post</li>
                        <li>All orders usually dispatched within 2 working days.</li>
                        <li>You will receive an email when your order has been dispatched.</li>
                    </ul>
                </li>
            </ul>
            TEXT,
    ],

    'generate_og_images' => (bool) env('GENERATE_OG_IMAGES', true),

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
            'stats' => 'cache.eating-out.stats',
            'top-rated-in-county' => 'coeliac.eating-out.top-rated-in-county.{county.slug}',
            'most-rated-in-county' => 'coeliac.eating-out.most-rated-in-county.{county.slug}',
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
    ],
];
