<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Resources\Recipes;

use App\Models\Recipes\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin Recipe */
class RecipeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => Str::of($this->title)->replace('&quot;', '"'),
            'link' => route('recipe.show', ['recipe' => $this]),
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'date' => $this->published,
            'description' => $this->meta_description,
        ];
    }
}
