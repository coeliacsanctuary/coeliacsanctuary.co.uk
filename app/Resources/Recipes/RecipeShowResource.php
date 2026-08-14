<?php

declare(strict_types=1);

namespace App\Resources\Recipes;

use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeNutrition;
use App\Resources\Collections\FeaturedInCollectionSimpleCardViewResource;
use App\Resources\Faqs\FaqResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin Recipe */
class RecipeShowResource extends JsonResource
{
    /** @return array */
    public function toArray(Request $request)
    {
        $this->load([
            'relatedRecipes', 'relatedRecipes.media',
            'associatedCollectionGroups', 'associatedCollectionGroups.group.collection', 'associatedCollectionGroups.group.collection.media',
            'faqs',
        ]);

        /** @var RecipeNutrition $nutrition */
        $nutrition = $this->nutrition;

        return [
            'id' => $this->id,
            'print_url' => route('recipe.print', ['recipe' => $this]),
            'title' => $this->title,
            'header_image_alt_text' => $this->header_image_alt_text,
            'short_title' => $this->short_title,
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'square_image' => $this->square_image_as_webp ?? $this->square_image,
            'published' => $this->published,
            'updated' => $this->lastUpdated,
            'author' => $this->author,
            'meta_description' => $this->meta_description,
            'description' => $this->description,
            'body' => $this->body ? Str::of($this->body)
                ->replace('&quot;', '"')
                ->markdown([
                    'renderer' => [
                        'soft_break' => '<br />',
                    ],
                ]) : null,
            'ingredients' => $this->processIngredients(),
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
            'featured_in' => FeaturedInCollectionSimpleCardViewResource::collection($this->associatedCollectionGroups),
            'faqs' => $this->faqs->isNotEmpty() ? FaqResource::collection($this->faqs) : null,
            'related_recipes' => RelatedRecipeCardViewResource::collection($this->relatedRecipes),
        ];
    }

    /** @return list<array{heading: string|null, items: list<string>}> */
    protected function processIngredients(): array
    {
        $groups = [];
        $group = ['heading' => null, 'items' => []];

        foreach (preg_split("/\r\n|\r|\n/", (string) $this->ingredients) ?: [] as $line) {
            $line = mb_trim($line);

            if ($line === '') {
                continue;
            }

            if (preg_match('#^</?(?:strong|b)>([^<]+)</?(?:strong|b)>$#i', $line, $matches) !== 1) {
                $group['items'][] = $line;

                continue;
            }

            if ($group['heading'] !== null || $group['items'] !== []) {
                $groups[] = $group;
            }

            $group = ['heading' => mb_trim($matches[1]), 'items' => []];
        }

        if ($group['heading'] !== null || $group['items'] !== []) {
            $groups[] = $group;
        }

        return $groups;
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
