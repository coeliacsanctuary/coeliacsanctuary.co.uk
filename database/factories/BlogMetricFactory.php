<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Blogs\BlogMetric;

class BlogMetricFactory extends Factory
{
    protected $model = BlogMetric::class;

    public function definition(): array
    {
        return [
            'blog_id' => BlogFactory::new(),
            'date' => today(),
            'page_views' => 0,
            'page_comment_views' => 0,
            'detail_card_views' => 0,
            'collection_card_views' => 0,
        ];
    }
}
