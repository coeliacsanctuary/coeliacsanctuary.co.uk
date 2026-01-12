<?php

declare(strict_types=1);

namespace App\Services\EatingOut;

use App\DataObjects\EatingOut\LatLng;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;
use Spatie\Geocoder\Geocoder;

class LocationSearchService
{
    public function __construct(protected Geocoder $geocoder)
    {
        //
    }

    public function getLatLng(string $term, bool $force = false): LatLng
    {
        /** @var array{lat: float, lng: float} $result */
        $result = $this->callSearchService($term, $force)->first();

        return new LatLng((float) $result['lat'], (float) $result['lng']);
    }

    /** @return Collection<int, array{lat: float, lng: float}> */
    protected function callSearchService(string $term, bool $force): Collection
    {
        /** @var array{lat: float, lng: float}[] $response */
        $response = $this->geocoder->getAllCoordinatesForAddress($term);

        if ((int) $response[0]['lat'] === 0) {
            throw new RuntimeException('Http request failed');
        }

        return collect($response)
            ->filter(fn (array $result) => $force || $this->isValidResult($result, $term))
            ->values();
    }

    protected function isValidResult(array $result, string $term): bool
    {
        return true;

        //        $keys = ['locality', 'archipelago'];
        //
        //        if (in_array(Arr::get($result, 'types.0'), $keys)) {
        //            return true;
        //        }
        //
        //        return (bool) (Str::of(Arr::get($result, 'formatted_address'))->lower()->contains(Str::lower($term)));
    }
}
