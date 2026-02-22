<?php

declare(strict_types=1);

namespace App\Ai\Tools;

use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeNutrition;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Tools\Request;
use Stringable;

class ViewRecipeTool extends BaseTool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return <<<'text'
        View a complete recipe, ingredients, method, nutrition, allergens, features, assigned meals etc.

        Do not give the user the entire recipe in your response, but a brief summary with a CTA to view the full recipe - guiding people around the website is the aim!

        Note, the recipe_slug is the final part of the url, eg /recipe/foo-bar-baz, `foo-bar-baz` is the slug.
        text;
    }

    /**
     * Execute the tool's logic.
     */
    protected function execute(Request $request): Stringable|string
    {
        $recipe = Recipe::query()
            ->where('slug', $request->string('recipe_slug')->toString())
            ->firstOrFail();

        /** @var RecipeNutrition $nutrition */
        $nutrition = $recipe->nutrition;

        $data = [
            'link' => $recipe->absolute_link,
            'title' => $recipe->title,
            'published' => $recipe->published,
            'updated' => $recipe->lastUpdated,
            'description' => $recipe->description,
            'ingredients' => $recipe->ingredients,
            'method' => $recipe->method,
            'features' => $recipe->features()->get()->map(fn (RecipeFeature $feature) => $feature->feature)->values(),
            'contains' => $recipe->containsAllergens()->map(fn (RecipeAllergen $allergen) => $allergen->allergen)->values(),
            'timing' => [
                'prep_time' => $recipe->prep_time,
                'cook_time' => $recipe->cook_time,
            ],
            'nutrition' => [
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

        return (string) json_encode($data);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'recipe_slug' => $schema->string()->required(),
        ];
    }
}
