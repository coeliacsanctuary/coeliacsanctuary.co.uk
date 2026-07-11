<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Collections\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
            'header_image_alt_text' => $this->header_image_alt_text,
            'published' => $this->published,
            'updated' => $this->lastUpdated,
            'description' => $this->description,
            'body' => Str::of($this->body)
                ->replace('&quot;', '"')
                ->markdown([
                    'renderer' => [
                        'soft_break' => '<br />',
                    ],
                ]),
            'groups' => CollectionGroupResource::collection($this->groups),
        ];
    }
}
