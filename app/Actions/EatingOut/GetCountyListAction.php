<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\Eatery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetCountyListAction
{
    /** @return Collection<string, array{list: Collection<int, object{country: string, county: string, county_slug: string, branches: int, eateries: int, attractions: int, hotels: int}>, counties: int, count: int}> */
    public function handle(): Collection
    {
        $key = config('coeliac.cacheable.eating-out.index-counts');

        return Cache::rememberForever(
            $key,
            fn () => $this->getCountries()->map(function (Collection $places) {
                $list = $places->map(fn ($county) => $this->formatCountry($county));

                return [
                    'list' => $list,
                    'counties' => $list->count(),
                    'eateries' => $list->pluck('total')->sum(),
                ];
            })
        );
    }

    /** @param  object{country: string, county: string, county_slug: string, branches: int, eateries: int, attractions: int, hotels: int}  $details */
    protected function formatCountry(object $details): object
    {
        return (object) [
            'name' => $details->county,
            'slug' => $details->county_slug,
            'eateries' => (int) $details->eateries,
            'attractions' => (int) $details->attractions,
            'hotels' => (int) $details->hotels,
            'branches' => (int) $details->branches,
            'total' => (int) $details->branches + (int) $details->eateries + (int) $details->hotels + (int) $details->attractions,
        ];
    }

    /** @return Collection<string, Collection<int, object{country: string, county: string, county_slug: string, branches: int, eateries: int, attractions: int, hotels: int}>> */
    protected function getCountries(): Collection
    {
        /** @var Collection<string, Collection<int, object{country: string, county: string, county_slug: string, branches: int, eateries: int, attractions: int, hotels: int}>> $countries */
        $countries = Eatery::query()
            ->selectRaw('wheretoeat_countries.country, wheretoeat_counties.county, wheretoeat_counties.slug county_slug')
            ->selectRaw('SUM(CASE WHEN wheretoeat.type_id = 1 THEN 1 ELSE 0 END) eateries')
            ->selectRaw('SUM(CASE WHEN wheretoeat.type_id = 2 THEN 1 ELSE 0 END) attractions')
            ->selectRaw('SUM(CASE WHEN wheretoeat.type_id = 2 THEN 1 ELSE 0 END) hotels')
            ->selectRaw('(select count(*) from wheretoeat_nationwide_branches wnb where wnb.live = 1 and wnb.county_id = wheretoeat_counties.id) branches')
            ->where('wheretoeat.live', true)
            ->where('wheretoeat_counties.county', '!=', 'Nationwide')
            ->where('wheretoeat_countries.country', '!=', 'Nationwide')
            ->leftJoin('wheretoeat_countries', 'country_id', 'wheretoeat_countries.id')
            ->leftJoin('wheretoeat_counties', 'county_id', 'wheretoeat_counties.id')
            ->leftJoin('wheretoeat_types', 'type_id', 'wheretoeat_types.id')
            ->groupBy('wheretoeat_countries.country', 'wheretoeat_counties.county', 'wheretoeat_counties.slug', 'wheretoeat_counties.id')
            ->orderBy('country')
            ->orderBy('county')
            ->get()
            ->groupBy('country');

        return $countries;
    }
}
