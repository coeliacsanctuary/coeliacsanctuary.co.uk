<?php

declare(strict_types=1);

namespace App\Builders\EatingOut;

use App\Concerns\EatingOut\QueriesNationwideEateries;
use App\DataObjects\EatingOut\LatLng;
use App\Models\EatingOut\Eatery;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<Eatery> */
class EateryBuilder extends Builder
{
    use QueriesNationwideEateries;

    public function selectDistance(LatLng $latLng, array $columns = []): static
    {
        return $this->selectRaw('(
                        6371000 * acos (
                          cos ( radians(?) )
                          * cos( radians( lat ) )
                          * cos( radians( lng ) - radians(?) )
                          + sin ( radians(?) )
                          * sin( radians( lat ) )
                        )
                     ) AS distance', [
            $latLng->lat,
            $latLng->lng,
            $latLng->lat,
        ])
            ->addSelect($columns);
    }

    public function hasCategories(array $categories): static
    {
        return $this->where(fn (Builder $builder) => $builder->whereHas('type', fn (Builder $builder) => $builder->whereIn('type', $categories)));
    }

    public function hasVenueTypes(array $venueTypes): static
    {
        return $this->where(fn (Builder $builder) => $builder->whereHas('venueType', fn (Builder $builder) => $builder->whereIn('slug', $venueTypes)));
    }

    public function hasFeatures(array $features): static
    {
        return $this->where(fn (Builder $builder) => $builder->whereHas('features', fn (Builder $builder) => $builder->whereIn('slug', $features)));
    }
}
