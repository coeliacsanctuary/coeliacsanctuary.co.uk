<?php

declare(strict_types=1);

namespace App\Resources\Recipes;

use App\Models\Recipes\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Recipe */
class RecipeSimpleCardViewResource extends JsonResource
{
    /** @return array{type: string, title: string, link: string, image: string, header_image_alt_text: string|null, square_image: string|null, date: string, nutrition: array{calories: int, servings: string, portion_size: string}} */
    public function toArray(Request $request)
    {
        /** @var int $calories */
        $calories = $this->nutrition?->calories;

        return [
            'type' => 'Recipe',
            'title' => $this->title,
            'link' => $this->link,
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'header_image_alt_text' => $this->header_image_alt_text,
            'square_image' => $this->square_image_as_webp ?? $this->square_image,
            'date' => $this->published,
            'nutrition' => [
                'calories' => $calories,
                'servings' => $this->servings,
                'portion_size' => $this->portion_size,
            ],
        ];
    }
}
