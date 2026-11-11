<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\GooglePlacesAutocompleteCache;
use Illuminate\Support\Str;
use SKAgarwal\GoogleApi\PlacesNew\GooglePlaces;

class AutocompleteLocationAction
{
    /** @return array<int, array{id: string, label: string}> */
    public function handle(string $term, string $sessionToken): array
    {
        $term = Str::of($term)->lower()->squish()->toString();

        if (mb_strlen($term) < 3) {
            return [];
        }

        $cached = GooglePlacesAutocompleteCache::query()->where('term', $term)->first();

        if ($cached?->isFresh()) {
            $cached->increment('hits');

            /** @var array<int, array{id: string, label: string}> $results */
            $results = $cached->response;

            return $results;
        }

        $response = app(GooglePlaces::class)->autocomplete($term, params: [
            'includedRegionCodes' => ['gb', 'ie', 'je', 'gg', 'im'],
            'sessionToken' => $sessionToken,
        ]);

        if ($response->failed()) {
            return [];
        }

        /** @var array<int, array{placePrediction?: array{placeId: string, text: array{text: string}}}> $suggestions */
        $suggestions = $response->json('suggestions', []);

        $results = collect($suggestions)
            ->pluck('placePrediction')
            ->filter()
            ->map(fn (array $prediction): array => [
                'id' => $prediction['placeId'],
                'label' => $prediction['text']['text'],
            ])
            ->values()
            ->all();

        GooglePlacesAutocompleteCache::query()->updateOrCreate(['term' => $term], [
            'response' => $results,
            'last_fetched_at' => now(),
        ]);

        return $results;
    }
}
