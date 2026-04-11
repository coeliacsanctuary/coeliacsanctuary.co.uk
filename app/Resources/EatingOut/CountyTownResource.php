<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryTown */
class CountyTownResource extends JsonResource
{
    /** @return array{name: string, link: string, eateries: int, attractions: int, hotels: int, total_eateries: int} */
    public function toArray(Request $request)
    {
        return [
            'name' => $this->town,
            'link' => $this->link(),
            'eateries' => $this->liveEateries->where('type_id', EateryType::EATERY)->count() + $this->liveBranches->count(),
            'attractions' => $this->liveEateries->where('type_id', EateryType::ATTRACTION)->count(),
            'hotels' => $this->liveEateries->where('type_id', EateryType::HOTEL)->count(),
            'total_eateries' => $this->liveEateries->count() + $this->liveBranches->count(),
        ];
    }
}
