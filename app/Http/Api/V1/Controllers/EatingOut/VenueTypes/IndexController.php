<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\VenueTypes;

use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class IndexController
{
    public function __invoke(): array
    {
        $venueTypes = EateryVenueType::query()
            ->orderBy('venue_type')
            ->get()
            ->groupBy('type_id')
            ->map(fn (Collection $options, int $typeId) => [
                'label' => Str::of(EateryType::from($typeId)->name)->title()->plural()->toString(),
                'options' => $options
                    ->map(fn (EateryVenueType $eateryVenueType) => [
                        'label' => $eateryVenueType->venue_type,
                        'value' => $eateryVenueType->id,
                    ]),
            ])
            ->values();

        return [
            'data' => $venueTypes,
        ];
    }
}
