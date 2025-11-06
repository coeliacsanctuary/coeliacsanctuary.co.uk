<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Geocode;

use App\Http\Api\V1\Requests\EatingOut\GeocodeRequest;
use Spatie\Geocoder\Geocoder;

class GetController
{
    public function __invoke(GeocodeRequest $request, Geocoder $geocoder): array
    {
        $result = $geocoder->getCoordinatesForAddress($request->string('term')->toString());

        return [
            'data' => [
                'lat' => $result['lat'],
                'lng' => $result['lng'],
            ],
        ];
    }
}
