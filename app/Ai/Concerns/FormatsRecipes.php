<?php

declare(strict_types=1);

namespace App\Ai\Concerns;

use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeNutrition;

trait FormatsRecipes
{
    protected function formatRecipe(Recipe $recipe): array
    {
        /** @var RecipeNutrition $nutrition */
        $nutrition = $recipe->nutrition;

        return [
            'id' => $recipe->id,
            'title' => $recipe->title,
            'description' => $recipe->meta_description,
            'link' => $recipe->absolute_link,
            'image' => $recipe->main_image,
            'nutritional_info' => [
                'servings' => $recipe->servings,
                'portion_size' => $recipe->portion_size,
                'calories' => $nutrition->calories,
                'carbs' => $nutrition->carbs,
                'fibre' => $nutrition->fibre,
                'fat' => $nutrition->fat,
                'sugar' => $nutrition->sugar,
                'protein' => $nutrition->protein,
            ],
        ];
    }
}
