<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\DataObjects\EatingOut\PendingEatery;
use Illuminate\Http\Request;

/** @mixin PendingEatery */
class EateryTripResource extends EateryBrowseResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request)
    {
        return [
            ...parent::toArray($request),
            'distanceAlongRoute' => round((float) $this->ordering),
            'distanceFromRoute' => round((float) $this->distance),
            'durationAlongRoute' => round((float) $this->durationAlongRoute),
        ];
    }
}
