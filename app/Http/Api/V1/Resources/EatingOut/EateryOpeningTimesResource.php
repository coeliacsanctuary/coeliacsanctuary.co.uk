<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Resources\EatingOut;

use App\Models\EatingOut\EateryOpeningTimes;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryOpeningTimes */
class EateryOpeningTimesResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request)
    {
        return [
            'is_open_now' => $this->is_open_now,
            'today' => [
                'opens' => $this->opensAt(),
                'closes' => $this->closesAt(),
            ],
            'days' => $this->opening_times_array,
        ];
    }
}
