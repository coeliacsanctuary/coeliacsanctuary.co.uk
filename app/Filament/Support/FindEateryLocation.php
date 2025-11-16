<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Filament\Dto\EateryLocationDto;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Support\Collection;

class FindEateryLocation
{
    public function handle(string $search): array
    {
        $results = collect();

        if (empty($search)) {
            return $results->toArray();
        }

        /** @var Collection<int, EateryLocationDto> $results */
        $results = $results
            ->merge($this->getCountyResults($search))
            ->merge($this->getTownResults($search))
            ->merge($this->getAreaResults($search))
            ->sortByDesc([fn (EateryLocationDto $a, EateryLocationDto $b) => levenshtein($a->matchedTerm, $search) <=> levenshtein($b->matchedTerm, $search)])
            ->mapWithKeys(fn (EateryLocationDto $dto) => ["{$dto->countryId}|{$dto->countyId}|{$dto->townId}|{$dto->areaId}" => $dto->label])
            ->take(10);

        return $results->toArray();
    }

    protected function getCountyResults(string $search): Collection|\Illuminate\Database\Eloquent\Collection
    {
        return EateryCounty::withoutGlobalScopes()
            ->whereLike('county', "%{$search}%")
            ->with(['country'])
            ->get()
            ->map(fn (EateryCounty $county) => new EateryLocationDto(
                type: 'county',
                label: "{$county->county}, {$county->country->country}",
                matchedTerm: $county->county,
                countryId: $county->country_id,
                countyId: $county->id,
                townId: null,
            ));
    }

    protected function getTownResults(string $search): Collection|\Illuminate\Database\Eloquent\Collection
    {
        return EateryTown::withoutGlobalScopes()
            ->whereLike('town', "%{$search}%")
            ->with(['county' => fn ($query) => $query->withoutGlobalScopes(), 'county.country'])
            ->get()
            ->map(fn (EateryTown $town) => new EateryLocationDto(
                type: $town->county->slug === 'london' ? 'borough' : 'town',
                label: "{$town->town}, {$town->county->county}, {$town->county->country->country}",
                matchedTerm: $town->town,
                countryId: $town->county->country_id,
                countyId: $town->county_id,
                townId: $town->id,
            ));
    }

    protected function getAreaResults(string $search): Collection|\Illuminate\Database\Eloquent\Collection
    {
        return EateryArea::withoutGlobalScopes()
            ->whereLike('area', "%{$search}%")
            ->with(['town' => fn ($query) => $query->withoutGlobalScopes(), 'town.county' => fn ($query) => $query->withoutGlobalScopes(), 'town.county.country'])
            ->get()
            ->map(fn (EateryArea $area) => new EateryLocationDto(
                type: 'area',
                label: "{$area->area}, {$area->town->town}, {$area->town->county->county}, {$area->town->county->country->country}",
                matchedTerm: $area->area,
                countryId: $area->town->county->country_id,
                countyId: $area->town->county_id,
                townId: $area->town_id,
                areaId: $area->id,
            ));
    }
}
