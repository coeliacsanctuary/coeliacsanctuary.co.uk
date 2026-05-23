<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Collections\CollectionGroupItem;
use App\Support\Collections\Collectable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CollectionGroupItem
 *
 * @property Collectable $item
 */
class CollectionGroupItemResource extends JsonResource
{
    /** @return array */
    public function toArray(Request $request)
    {
        return [
            'type' => class_basename($this->item),
            'title' => $this->item_title ?? $this->item->title,
            'description' => $this->item_description ?? $this->item->meta_description,
            'image' => $this->item->main_image_as_webp ?? $this->item->main_image,
            'square_image' => $this->item->square_image_as_webp ?? $this->item->square_image,
            'date' => $this->item->lastUpdated,
            'link' => $this->item->link,
        ];
    }
}
