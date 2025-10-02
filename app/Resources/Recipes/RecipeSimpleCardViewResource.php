<?php

declare(strict_types=1);

namespace App\Resources\Recipes;

use App\Models\Recipes\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Recipe */
class RecipeSimpleCardViewResource extends JsonResource
{
    /** @return array{title: string, link: string, image: string|null} */
    public function toArray(Request $request)
    {
        return [
            'title' => $this->title,
            'link' => $this->link,
            'image' => $this->square_image_as_webp ?? $this->square_image,
        ];
    }
}
