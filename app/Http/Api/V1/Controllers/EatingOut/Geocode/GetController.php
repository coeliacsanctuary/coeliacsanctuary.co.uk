<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Geocode;

use App\Http\Api\V1\Requests\EatingOut\GeocodeRequest;
use App\Services\EatingOut\LocationSearchService;

class GetController
{
    public function __invoke(GeocodeRequest $request): array
    {
        $result = app(LocationSearchService::class)->getLatLng($request->string('term')->toString());

        return [
            'data' => $result->toLatLng(),
        ];
    }
}
