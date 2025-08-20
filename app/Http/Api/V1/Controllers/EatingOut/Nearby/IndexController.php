<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Nearby;

use App\DataObjects\EatingOut\LatLng;
use App\Http\Api\V1\Requests\EatingOut\NearbyRequest;
use App\Http\Api\V1\Resources\EatingOut\NearbyEateryResource;
use App\Pipelines\EatingOut\GetEateries\GetNearbyEateriesPipeline;

class IndexController
{
    public function __invoke(NearbyRequest $request, GetNearbyEateriesPipeline $getNearbyEateriesPipeline): array
    {
        /** @var float[] $latLng */
        $latLng = $request->string('latlng')
            ->explode(',')
            ->map(fn (string $value) => (float) $value)
            ->toArray();

        return [
            'data' => $getNearbyEateriesPipeline->run(new LatLng($latLng[0], $latLng[1], radius: 5), NearbyEateryResource::class),
        ];
    }
}
