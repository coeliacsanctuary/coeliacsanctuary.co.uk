<?php

declare(strict_types=1);

namespace App\Resources\Recipes;

use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeFeature;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Recipe */
class RecipeDetailCardViewResource extends JsonResource
{
    /** @return array{title: string, link: string, image: string, header_image_alt_text: string|null, square_image: string|null, date: string, description: string, features: list<array{feature: string, slug: string}>, nutrition: array{calories: int, servings: string, portion_size: string}} */
    public function toArray(Request $request)
    {
        /** @var int $calories */
        $calories = $this->nutrition?->calories;

        return [
            'title' => $this->title,
            'link' => $this->link,
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'header_image_alt_text' => $this->header_image_alt_text,
            'square_image' => $this->square_image_as_webp ?? $this->square_image,
            'date' => $this->published,
            'description' => $this->meta_description,
            'features' => array_values($this->features->map($this->processFeature(...))->all()),
            'nutrition' => [
                'calories' => $calories,
                'servings' => $this->servings,
                'portion_size' => $this->portion_size,
            ],
        ];
    }

    /** @return array{feature: string, slug: string} */
    protected function processFeature(RecipeFeature $feature): array
    {
        return [
            'feature' => $feature->feature,
            'slug' => $feature->slug,
        ];
    }
}
