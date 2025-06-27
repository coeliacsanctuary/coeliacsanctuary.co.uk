<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\DataObjects\EatingOut\LatLng;
use App\Models\EatingOut\EateryTown;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin EateryTown */
class LondonBoroughResource extends JsonResource
{
    /** @return array{name: string, description: string|null, latlng:LatLng, link: string, area_count: int, top_areas: Collection<int, string>, locations: int} */
    public function toArray(Request $request)
    {
        return [
            'name' => $this->town,
            'description' => $this->description,
            'latlng' => LatLng::fromString((string)$this->latlng),
            /** @phpstan-ignore-next-line  */
            'link' => $this->areas->count() > 1 ? $this->link() : $this->areas->first()->link(),
            'area_count' => $this->areas->count(),
            'top_areas' => $this->areas->sortByDesc('eateries_count')->take(3)->pluck('area'),
            'locations' => $this->liveEateries->count() + $this->liveBranches->count(),
        ];
    }
}
