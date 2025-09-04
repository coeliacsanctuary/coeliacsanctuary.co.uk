<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Collections\CollectionItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CollectionItem
 *
 * @property CollectionItem $item
 */
class CollectedItemResource extends JsonResource
{
    /** @return array */
    public function toArray(Request $request)
    {
        return [
            'type' => class_basename($this->item),
            'title' => $this->item->title,
            'image' => $this->item->main_image_as_webp ?? $this->item->main_image,
            'square_image' => $this->item->square_image_as_webp ?? $this->item->square_image,
            'date' => $this->item->lastUpdated,
            'description' => $this->item->meta_description,
            'link' => $this->item->link,
        ];
    }
}
