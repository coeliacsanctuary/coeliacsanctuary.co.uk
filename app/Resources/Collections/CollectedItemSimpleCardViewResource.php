<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Blogs\Blog;
use App\Models\Collections\CollectionGroupItem;
use App\Models\Recipes\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CollectionGroupItem
 */
class CollectedItemSimpleCardViewResource extends JsonResource
{
    /** @return array{type: string, title: string, link: string, image: string, square_image: string} */
    public function toArray(Request $request)
    {
        /** @var Blog | Recipe $item */
        $item = $this->item;

        return [
            'type' => class_basename($item),
            'title' => $item->title,
            'link' => $item->link,
            'image' => $item->main_image_as_webp ?? $item->main_image,
            'header_image_alt_text' => $item->header_image_alt_text,
            'square_image' => $item->square_image_as_webp ?? $item->square_image,
        ];
    }
}
