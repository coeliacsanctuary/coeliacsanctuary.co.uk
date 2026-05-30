<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Collections\Collection;
use App\Models\Collections\CollectionGroup;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Collection */
class CollectionSimpleCardViewResource extends JsonResource
{
    /** @return array{title: string, link: string, description: string} */
    public function toArray(Request $request)
    {
        /** @var CollectionGroup $firstGroup */
        $firstGroup = $this->groups->first();

        $items = $firstGroup
            ->items()
            ->take($this->items_to_display)
            ->with(['item', 'item.media'])
            ->whereNotIn('item_type', [Eatery::class, NationwideBranch::class])
            ->get();

        return [
            'title' => $this->title,
            'link' => route('collection.show', ['collection' => $this]),
            'description' => $this->meta_description,
            'items_to_display' => $this->items_to_display,
            'items' => CollectedItemSimpleCardViewResource::collection($items),
        ];
    }
}
