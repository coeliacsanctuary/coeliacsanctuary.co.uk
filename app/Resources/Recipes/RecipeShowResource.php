<?php

declare(strict_types=1);

namespace App\Resources\Recipes;

use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeNutrition;
use App\Resources\Collections\FeaturedInCollectionSimpleCardViewResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin Recipe */
class RecipeShowResource extends JsonResource
{
    /** @return array */
    public function toArray(Request $request)
    {
        $this->load(['associatedCollections', 'associatedCollections.collection', 'associatedCollections.collection.media']);

        /** @var RecipeNutrition $nutrition */
        $nutrition = $this->nutrition;

        return [
            'id' => $this->id,
            'print_url' => route('recipe.print', ['recipe' => $this]),
            'title' => $this->title,
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'square_image' => $this->square_image_as_webp ?? $this->square_image,
            'published' => $this->published,
            'updated' => $this->lastUpdated,
            'author' => $this->author,
            'description' => $this->description,
            'ingredients' => Str::markdown($this->ingredients, [
                'renderer' => [
                    'soft_break' => '<br />',
                ],
            ]),
            'method' => Str::markdown($this->method),
            'features' => $this->features()->get()->map($this->processFeature(...))->values(),
            'allergens' => $this->containsAllergens()->map($this->processAllergen(...))->values(),
            'timing' => [
                'prep_time' => $this->prep_time,
                'cook_time' => $this->cook_time,
            ],
            'nutrition' => [
                'servings' => $this->servings,
                'portion_size' => $this->portion_size,
                'calories' => $nutrition->calories,
                'carbs' => $nutrition->carbs,
                'fibre' => $nutrition->fibre,
                'fat' => $nutrition->fat,
                'sugar' => $nutrition->sugar,
                'protein' => $nutrition->protein,
            ],
            'featured_in' => FeaturedInCollectionSimpleCardViewResource::collection($this->associatedCollections),
        ];
    }

    protected function processFeature(RecipeFeature $feature): array
    {
        return [
            'feature' => $feature->feature,
            'slug' => $feature->slug,
        ];
    }

    protected function processAllergen(RecipeAllergen $allergen): array
    {
        return [
            'allergen' => $allergen->allergen,
            'slug' => $allergen->slug,
        ];
    }
}
