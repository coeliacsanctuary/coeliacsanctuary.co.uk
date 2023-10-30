<?php

declare(strict_types=1);

namespace App\Services\EatingOut;

use App\DataObjects\EatingOut\LatLng;
use Illuminate\Support\Collection;
use RuntimeException;
use Spatie\Geocoder\Geocoder;

class LocationSearchService
{
    public function __construct(protected Geocoder $geocoder)
    {
        //
    }

    public function getLatLng(string $term): LatLng
    {
        /** @var array{lat: float, lng: float} $result */
        $result = $this->callSearchService($term)->first();

        return new LatLng((float) $result['lat'], (float) $result['lng']);
    }

    /** @return Collection<int, array{lat: float, lng: float}> */
    protected function callSearchService(string $term): Collection
    {
        /** @var array{lat: float, lng: float} $response */
        $response = $this->geocoder->getCoordinatesForAddress($term);

        if ((int) $response['lat'] === 0) {
            throw new RuntimeException('Http request failed');
        }

        return collect([$response]);
    }
}
