<?php

declare(strict_types=1);

namespace App\Actions\EatingOut\TripPlanner;

use App\Models\OpenRouteServiceAutocompleteCache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AutocompleteLocationAction
{
    /** @return array<int, array{id: string, label: string, lat: float, lng: float}> */
    public function handle(string $term): array
    {
        $term = Str::of($term)->lower()->squish()->toString();

        if (mb_strlen($term) < 3) {
            return [];
        }

        $cached = OpenRouteServiceAutocompleteCache::query()->where('term', $term)->first();

        if ($cached) {
            $cached->update([
                'hits' => $cached->hits + 1,
                'most_recent_hit' => now(),
            ]);

            /** @var array<int, array{id: string, label: string, lat: float, lng: float}> $results */
            $results = $cached->response;

            return $results;
        }

        $response = Http::openRouteService()
            ->get('geocode/autocomplete', [
                'text' => $term,
                'boundary.country' => 'GB,IE',
                'layers' => 'locality,localadmin,postalcode,county',
            ])
            ->throw();

        /** @var array<int, array{properties: array{id: string, label: string}, geometry: array{coordinates: array{float, float}}}> $features */
        $features = $response->json('features', []);

        $results = collect($features)
            ->map(fn (array $feature): array => [
                'id' => $feature['properties']['id'],
                'label' => $feature['properties']['label'],
                'lat' => (float) $feature['geometry']['coordinates'][1],
                'lng' => (float) $feature['geometry']['coordinates'][0],
            ])
            ->unique('label')
            ->values()
            ->all();

        OpenRouteServiceAutocompleteCache::query()->create([
            'term' => $term,
            'response' => $results,
        ]);

        return $results;
    }
}
