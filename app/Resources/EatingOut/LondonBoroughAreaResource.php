<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryArea */
class LondonBoroughAreaResource extends JsonResource
{
    /** @return array{name: string, link: string, eateries: int, attractions: int, hotels: int} */
    public function toArray(Request $request)
    {
        return [
            'name' => $this->area,
            'link' => $this->link(),
            'eateries' => $this->liveEateries->where('type_id', EateryType::EATERY)->count() + $this->liveBranches->count(),
            'attractions' => $this->liveEateries->where('type_id', EateryType::ATTRACTION)->count(),
            'hotels' => $this->liveEateries->where('type_id', EateryType::HOTEL)->count(),
        ];
    }
}
