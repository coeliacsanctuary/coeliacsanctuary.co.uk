<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Collections\CollectionGroup;
use App\Models\Collections\CollectionItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CollectionGroup
 */
class CollectionGroupResource extends JsonResource
{
    /** @return array */
    public function toArray(Request $request)
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'items' => CollectionGroupItemResource::collection($this->items),
        ];
    }
}
