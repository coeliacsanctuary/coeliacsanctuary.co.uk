<?php

declare(strict_types=1);

namespace App\Actions\Shop\TravelCardSearch;

use App\Models\Shop\TravelCardSearchTerm;
use App\Support\Helpers;
use Carbon\CarbonInterval;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetPopularTravelCardDestinationsAction
{
    /** @return Collection<int, array{term: string, flag: string|null}> */
    public function handle(): Collection
    {
        /** @var Collection<int, array{term: string, flag: string|null}> $destinations */
        $destinations = Cache::flexible(
            'travel-card-popular-destinations',
            [CarbonInterval::hours(12), CarbonInterval::hour()],
            fn () => TravelCardSearchTerm::query()
                ->where('type', 'country')
                ->whereHas('products')
                ->orderByDesc('hits')
                ->orderBy('term')
                ->take(10)
                ->get()
                ->map(fn (TravelCardSearchTerm $term) => [
                    'term' => $term->display_term,
                    'flag' => Helpers::countryCode($term->term),
                ])
                ->values(),
        );

        return $destinations;
    }
}
