<?php

declare(strict_types=1);

namespace App\Services\EatingOut;

use App\DataObjects\EatingOut\LatLng;
use App\Models\GoogleGeocodeCache;
use Illuminate\Support\Collection;
use RuntimeException;
use Spatie\Geocoder\Geocoder;

class LocationSearchService
{
    public function __construct(protected Geocoder $geocoder)
    {
        //
    }

    /**
     * @param callable(array, string): bool $checkResult
     * @return ($nullIfEmpty is true ? null | ($raw is true ? array<string, mixed> : LatLng) : ($raw is true ? array<string, mixed> : LatLng))
     */
    public function getLatLng(string $term, bool $force = false, bool $raw = false, bool $nullIfEmpty = false, ?callable $checkResult = null): null | LatLng | array
    {
        if ($this->termIsCached($term)) {
            /** @var GoogleGeocodeCache $result */
            $result = GoogleGeocodeCache::query()->where('term', $term)->first();

            $result->update([
                'hits' => $result->hits + 1,
                'most_recent_hit' => now(),
            ]);

            if ($raw) {
                return $result->response;
            }

            return $result->toLatLng();
        }

        /** @var array{lat: float, lng: float} $result */
        $result = $this->callSearchService($term, $force, $checkResult)->first();

        /** @phpstan-ignore-next-line  */
        if ($nullIfEmpty && ! $result) {
            return null;
        }

        $this->cacheResult($term, $result);

        if ($raw) {
            return $result;
        }

        return new LatLng((float) $result['lat'], (float) $result['lng']);
    }

    protected function termIsCached(string $term): bool
    {
        return GoogleGeocodeCache::query()->where('term', $term)->exists();
    }

    protected function cacheResult(string $term, array $result): void
    {
        GoogleGeocodeCache::query()->create([
            'term' => $term,
            'response' => $result,
        ]);
    }

    /** @return Collection<int, array{lat: float, lng: float}> */
    protected function callSearchService(string $term, bool $force, ?callable $checkResult = null): Collection
    {
        /** @var array{lat: float, lng: float}[] $response */
        $response = $this->geocoder->getAllCoordinatesForAddress($term);

        if ((int) $response[0]['lat'] === 0) {
            throw new RuntimeException('Http request failed');
        }

        return collect($response)
            ->filter(fn (array $result) => $force || $this->isValidResult($result, $term, $checkResult))
            ->values();
    }

    protected function isValidResult(array $result, string $term, ?callable $checkResult = null): bool
    {
        if ($checkResult) {
            return $checkResult($result, $term);
        }

        return true;
    }
}
