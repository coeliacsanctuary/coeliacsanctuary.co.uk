<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetCountyListAction
{
    /** @return Collection<int, array{name: string, description: string, list: Collection<int, array>, counties: int, eateries: mixed, top_counties: Collection<int,array>}> */
    public function handle(): Collection
    {
        $key = config('coeliac.cacheable.eating-out.index-counts');

        $eateryCounts = [
            'eateries as total_eateries_count',
            'eateries as eateries_count' => fn ($query) => $query->where('type_id', EateryType::EATERY->value),
            'eateries as attractions_count' => fn ($query) => $query->where('type_id', EateryType::ATTRACTION->value),
            'eateries as hotels_count' => fn ($query) => $query->where('type_id', EateryType::HOTEL->value),
        ];

        return Cache::rememberForever(
            $key,
            fn () => EateryCountry::query()
                ->withCount($eateryCounts)
                ->with(['counties' => fn ($query) => $query
                    ->chaperone()
                    ->with(['media'])
                    ->withCount([...$eateryCounts, 'nationwideBranches', 'reviews as review_count'])
                    ->withAvg('reviews as avg_rating', 'rating')
                    ->orderBy('county'),
                ])
                ->orderByDesc('total_eateries_count')
                ->whereNot('country', 'Nationwide')
                ->get()
                ->map(fn (EateryCountry $country) => [
                    'name' => $country->country,
                    'description' => app(GetCountryDescriptionAction::class)->handle($country),
                    'list' => $country->counties->map($this->formatCounty(...)),
                    'counties' => $country->counties->count(),
                    'eateries' => $country->getAttribute('total_eateries_count'),
                    'top_counties' => $country->counties
                        ->filter(fn (EateryCounty $county) => (int) $county->getAttribute('review_count') > 0)
                        ->sortByDesc(fn (EateryCounty $county) => $this->bayesianScore($county))
                        ->take(3)
                        ->map($this->formatCounty(...))
                        ->values(),
                ])
        );
    }

    protected function bayesianScore(EateryCounty $county): float
    {
        $n = (int) $county->getAttribute('review_count');
        $avg = (float) ($county->getAttribute('avg_rating') ?? 0);
        $C = 5;
        $m = 4.0;

        return ($n * $avg + $C * $m) / ($n + $C);
    }

    protected function formatCounty(EateryCounty $county): array
    {
        return [
            'name' => $county->county,
            'slug' => $county->slug,
            'image' => $county->image ?? $county->country?->image,
            'eateries' => $county->getAttribute('eateries_count'),
            'attractions' => $county->getAttribute('attractions_count'),
            'hotels' => $county->getAttribute('hotels_count'),
            'branches' => $county->nationwide_branches_count,
            'total' => (int) $county->getAttribute('total_eateries_count') + (int) $county->nationwide_branches_count,
            'review_count' => $county->getAttribute('review_count'),
            'avg_rating' => $county->getAttribute('avg_rating'),
        ];
    }
}
