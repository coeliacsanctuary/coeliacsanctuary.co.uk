<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Collections\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Collection */
class CollectionSimpleCardViewResource extends JsonResource
{
    /** @return array{title: string, link: string, description: string} */
    public function toArray(Request $request)
    {
        $this->load(['items' => fn (Relation $relation) => $relation->take(3), 'items.item', 'items.item.media']);

        return [
            'title' => $this->title,
            'link' => route('collection.show', ['collection' => $this]),
            'description' => $this->meta_description,
            'items' => CollectedItemSimpleCardViewResource::collection($this->items),
        ];
    }
}
