<?php

declare(strict_types=1);

namespace App\Resources\Recipes;

use App\Models\Recipes\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Recipe */
class RecipeApiResource extends JsonResource
{
    /** @return array{id: int, title: string, link: string, main_image: string, created_at: string, meta_description: string, description: string} */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'link' => $this->link,
            'main_image' => $this->main_image_as_webp ?? $this->main_image,
            'created_at' => $this->published,
            'meta_description' => $this->meta_description,
            'description' => $this->description,
        ];
    }
}
