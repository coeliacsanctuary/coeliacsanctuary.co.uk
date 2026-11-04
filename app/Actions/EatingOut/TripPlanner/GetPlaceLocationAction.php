<?php

declare(strict_types=1);

namespace App\Actions\EatingOut\TripPlanner;

use App\DataObjects\EatingOut\LatLng;
use App\Models\GooglePlacesDetailsCache;
use RuntimeException;
use SKAgarwal\GoogleApi\PlacesNew\GooglePlaces;

class GetPlaceLocationAction
{
    public function handle(string $placeId, string $sessionToken): LatLng
    {
        $cached = GooglePlacesDetailsCache::query()->where('place_id', $placeId)->first();

        if ($cached?->isFresh()) {
            $cached->increment('hits');

            return new LatLng((float) $cached->response['lat'], (float) $cached->response['lng']);
        }

        $response = app(GooglePlaces::class)->placeDetails($placeId, ['location'], ['sessionToken' => $sessionToken]);

        throw_if($response->failed(), new RuntimeException("Unable to find the location for place {$placeId}"));

        $location = [
            'lat' => (float) $response->json('location.latitude'),
            'lng' => (float) $response->json('location.longitude'),
        ];

        GooglePlacesDetailsCache::query()->updateOrCreate(['place_id' => $placeId], [
            'response' => $location,
            'last_fetched_at' => now(),
        ]);

        return new LatLng($location['lat'], $location['lng']);
    }
}
