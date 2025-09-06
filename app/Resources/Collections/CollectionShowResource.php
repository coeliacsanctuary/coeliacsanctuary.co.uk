<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Collections\Collection;
use App\Models\Collections\CollectionItem;
use App\ResourceCollections\Collections\CollectedItemCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Collection */
class CollectionShowResource extends JsonResource
{
    /** @return array */
    public function toArray(Request $request)
    {
        $items = $this->items->filter(fn (CollectionItem $collectionItem) => $collectionItem->item !== null);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'published' => $this->published,
            'updated' => $this->lastUpdated,
            'description' => $this->description,
            'body' => $this->body,
            'items' => new CollectedItemCollection($items),
        ];
    }
}
