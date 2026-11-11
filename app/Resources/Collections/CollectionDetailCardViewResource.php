<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Collections\Collection as CollectionModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CollectionModel */
class CollectionDetailCardViewResource extends JsonResource
{
    /** @return array{title: string, link: string, image: string, header_image_alt_text: string|null, date: string, description: string, items: array{recipes: int, blogs: int, eateries: int}} */
    public function toArray(Request $request)
    {
        return [
            'title' => $this->title,
            'link' => $this->link,
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'header_image_alt_text' => $this->header_image_alt_text,
            'date' => $this->lastUpdated ?? $this->published,
            'description' => $this->meta_description,
            'items' => [
                'recipes' => $this->recipes_count,
                'blogs' => $this->blogs_count,
                'eateries' => $this->eateries_count,
            ],
        ];
    }
}
