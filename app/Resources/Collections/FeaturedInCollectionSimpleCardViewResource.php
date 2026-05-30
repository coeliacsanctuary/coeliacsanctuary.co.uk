<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroupItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CollectionGroupItem */
class FeaturedInCollectionSimpleCardViewResource extends JsonResource
{
    /** @return array{title: string, link: string} */
    public function toArray(Request $request)
    {
        /** @var Collection $collection */
        $collection = $this->group?->collection;

        return [
            'title' => $collection->title,
            'link' => route('collection.show', ['collection' => $collection]),
            'image' => $collection->main_image_as_webp ?? $collection->main_image,
            'description' => $collection->meta_description,
        ];
    }
}
