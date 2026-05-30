<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Collections\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Collection */
class CollectionShowResource extends JsonResource
{
    /** @return array */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'published' => $this->published,
            'updated' => $this->lastUpdated,
            'description' => $this->description,
            'body' => $this->body,
            'groups' => CollectionGroupResource::collection($this->groups),
        ];
    }
}
