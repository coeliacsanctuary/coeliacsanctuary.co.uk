<?php

declare(strict_types=1);

namespace App\Resources\Collections;

use App\Models\Collections\Collection as CollectionModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CollectionModel */
class CollectionDetailCardViewResource extends JsonResource
{
    /** @return array{title: string, link: string, image: string, date: string, description: string, number_of_items: int} */
    public function toArray(Request $request)
    {
        return [
            'title' => $this->title,
            'link' => $this->link,
            'image' => $this->main_image_as_wepb ?? $this->main_image,
            'date' => $this->lastUpdated,
            'description' => $this->meta_description,
            'number_of_items' => $this->items_count,
        ];
    }
}
