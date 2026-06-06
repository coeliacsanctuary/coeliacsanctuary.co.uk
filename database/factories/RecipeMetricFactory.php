<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Recipes\RecipeMetric;

class RecipeMetricFactory extends Factory
{
    protected $model = RecipeMetric::class;

    public function definition(): array
    {
        return [
            'recipe_id' => RecipeFactory::new(),
            'date' => today(),
            'page_views' => 0,
            'page_comment_views' => 0,
            'detail_card_views' => 0,
            'collection_card_views' => 0,
        ];
    }
}
