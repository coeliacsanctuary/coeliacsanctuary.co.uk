<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Collections\CollectionGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
            'body' => $this->body ? Str::of($this->body)
                ->replace('&quot;', '"')
                ->markdown([
                    'renderer' => [
                        'soft_break' => '<br />',
                    ],
                ]) : null,
            'items' => CollectionGroupItemResource::collection($this->items),
        ];
    }
}
