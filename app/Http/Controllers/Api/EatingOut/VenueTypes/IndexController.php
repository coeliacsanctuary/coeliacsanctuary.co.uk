<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\VenueTypes;

use App\Models\EatingOut\EateryVenueType;
use Illuminate\Support\Collection;

class IndexController
{
    /** @return Collection<int, array{id: int, type_id: int<0, max>, label: string, count: int<0, max>}> */
    public function __invoke(): Collection
    {
        return EateryVenueType::query()
            ->withCount('eateries')
            ->orderBy('venue_type')
            ->get()
            ->map(fn (EateryVenueType $eateryVenueType) => [
                'id' => $eateryVenueType->id,
                'type_id' => $eateryVenueType->type_id,
                'label' => $eateryVenueType->venue_type,
                'count' => $eateryVenueType->eateries_count,
            ]);
    }
}
