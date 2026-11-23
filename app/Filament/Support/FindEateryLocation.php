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
        if (empty($search)) {
            return [];
        }

        return $this->getCountyResults($search)
            ->merge($this->getTownResults($search))
            ->merge($this->getAreaResults($search))
            ->sortBy(fn (EateryLocationDto $dto): int => levenshtein($dto->matchedTerm, $search))
            ->mapWithKeys(fn (EateryLocationDto $dto): array => ["{$dto->countryId}|{$dto->countyId}|{$dto->townId}|{$dto->areaId}" => $dto->label])
            ->take(10)
            ->all();
    }

    /** @return Collection<int, EateryLocationDto> */
    protected function getCountyResults(string $search): Collection
    {
        return EateryCounty::query()->withoutGlobalScopes()
            ->whereLike('county', "%{$search}%")
            ->with(['country'])
            ->get()
            ->toBase()
            ->map(fn (EateryCounty $county): EateryLocationDto => new EateryLocationDto(
                type: 'county',
                label: "{$county->county}, {$county->country?->country}",
                matchedTerm: $county->county,
                countryId: $county->country_id,
                countyId: $county->id,
                townId: null,
            ));
    }

    /** @return Collection<int, EateryLocationDto> */
    protected function getTownResults(string $search): Collection
    {
        return EateryTown::query()->withoutGlobalScopes()
            ->whereLike('town', "%{$search}%")
            ->with(['county' => fn ($query) => $query->withoutGlobalScopes(), 'county.country'])
            ->get()
            ->toBase()
            ->map(function (EateryTown $town): ?EateryLocationDto {
                $county = $town->county;

                if ( ! $county) {
                    return null;
                }

                return new EateryLocationDto(
                    type: $county->slug === 'london' ? 'borough' : 'town',
                    label: "{$town->town}, {$county->county}, {$county->country?->country}",
                    matchedTerm: $town->town,
                    countryId: $county->country_id,
                    countyId: $town->county_id,
                    townId: $town->id,
                );
            })
            ->filter()
            ->values();
    }

    /** @return Collection<int, EateryLocationDto> */
    protected function getAreaResults(string $search): Collection
    {
        return EateryArea::query()->withoutGlobalScopes()
            ->whereLike('area', "%{$search}%")
            ->with(['town' => fn ($query) => $query->withoutGlobalScopes(), 'town.county' => fn ($query) => $query->withoutGlobalScopes(), 'town.county.country'])
            ->get()
            ->toBase()
            ->map(function (EateryArea $area): ?EateryLocationDto {
                $town = $area->town;
                $county = $town?->county;

                if ( ! $town || ! $county) {
                    return null;
                }

                return new EateryLocationDto(
                    type: 'area',
                    label: "{$area->area}, {$town->town}, {$county->county}, {$county->country?->country}",
                    matchedTerm: $area->area,
                    countryId: $county->country_id,
                    countyId: $town->county_id,
                    townId: $area->town_id,
                    areaId: $area->id,
                );
            })
            ->filter()
            ->values();
    }
}
