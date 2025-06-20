<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\DataObjects\EatingOut\LatLng;
use App\Models\EatingOut\EateryTown;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryTown */
class LondonBoroughResource extends JsonResource
{
    /** @return array{name: string, link: string, eateries: int, attractions: int, hotels: int} */
    public function toArray(Request $request)
    {
        return [
            'name' => $this->town,
            'description' => $this->description,
            'latlng' => LatLng::fromString($this->latlng),
            'link' => $this->areas->count() > 1 ? $this->link() : $this->areas->first()->link(),
            'area_count' => $this->areas->count(),
            'top_areas' => $this->areas->sortByDesc('eateries_count')->take(3)->pluck('area'),
            'locations' => $this->liveEateries->count() + $this->liveBranches->count(),
        ];
    }
}
