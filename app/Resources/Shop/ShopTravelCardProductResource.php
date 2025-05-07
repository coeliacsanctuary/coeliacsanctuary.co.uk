<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopProduct;
use App\Models\Shop\TravelCardSearchTerm;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use League\ISO3166\ISO3166;

/** @mixin ShopProduct */
class ShopTravelCardProductResource extends ShopProductResource
{
    public function toArray(Request $request): array
    {
        return array_merge([
            'is_travel_card' => true,
            'countries' => $this->whenLoaded(
                'travelCardSearchTerms',
                fn () => $this->getCountries(),
            ),
        ], parent::toArray($request));
    }

    /** @return Collection<int, array{languages: string, countries: array{country: string, code: string}}> */
    protected function getCountries(): Collection
    {
        /** @var Collection<int, Collection<int, TravelCardSearchTerm>> $countries */
        $countries = $this
            ->travelCardSearchTerms
            ->filter(fn (TravelCardSearchTerm $term) => $term->pivot->card_show_on_product_page) /** @phpstan-ignore-line */
            ->groupBy('pivot.card_language')
            ->when(
                fn (Collection $collection) => $collection->has('both'),
                /** @param Collection<string, Collection<int, TravelCardSearchTerm>> $collection */
                function (Collection $collection): void {
                    $keys = Str::of($this->title)
                        ->before(' Coeliac Gluten Free Travel Translation Card')
                        ->explode(' and ')
                        ->toArray();

                    /** @var Collection<int, TravelCardSearchTerm> $both */
                    $both = $collection->get('both');

                    $both->each(function (TravelCardSearchTerm $term) use ($keys, $collection): void {
                        foreach ($keys as $key) {
                            $collection->has($key) ?: $collection->put($key, collect()); /** @phpstan-ignore-line */

                            /** @var Collection<int, TravelCardSearchTerm> $currentItems */
                            $currentItems = $collection->get($key);

                            $collection->put($key, $currentItems->push($term)); /** @phpstan-ignore-line */
                        }
                    });
                }
            )
            ->forget('both');

        /** @var Collection<int, array{languages: string, countries: array{country: string, code: string}}> $sortedCountries */
        /** @phpstan-ignore-next-line  */
        $sortedCountries = $countries->map(fn (Collection $countries, string $language) => [
            'language' => $language,
            'countries' => $countries
                ->sortBy([
                    /** @phpstan-ignore-next-line */
                    fn (TravelCardSearchTerm $a, TravelCardSearchTerm $b) => $b->pivot->card_score <=> $a->pivot->card_score,
                    fn (TravelCardSearchTerm $a, TravelCardSearchTerm $b) => Str::lower($a->term) <=> Str::lower($b->term),
                ])
                ->map(fn (TravelCardSearchTerm $term) => [
                    'country' => Str::title($term->term),
                    'code' => $this->countryCode($term->term),
                ])
                ->unique('country')
                ->values()
                ->toArray(),
        ]);

        return $sortedCountries->values();
    }

    protected function countryCode(string $country): ?string
    {
        try {
            return match (Str::lower($country)) {
                'england' => 'gb-eng',
                'wales' => 'gb-wls',
                'scotland', 'orkney islands', 'shetland islands' => 'gb-sct',
                'america', 'usa' => 'us',
                'channel islands' => 'gb',
                'czech republic' => 'cz',
                default => Str::lower(Arr::get(app(ISO3166::class)->name($country), 'alpha2')),
            };
        } catch (Exception) {
            return null;
        }
    }
}
