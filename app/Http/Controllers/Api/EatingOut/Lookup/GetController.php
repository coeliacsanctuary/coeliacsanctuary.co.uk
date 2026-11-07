<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Lookup;

use App\Actions\EatingOut\GetPlaceLocationAction;
use App\Http\Requests\EatingOut\Api\LookupGetRequest;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class GetController
{
    public function __invoke(string $placeId, LookupGetRequest $request, GetPlaceLocationAction $getPlaceLocationAction): JsonResponse
    {
        try {
            $latLng = $getPlaceLocationAction->handle($placeId, $request->string('sessionToken')->toString());
        } catch (RuntimeException) {
            abort(404);
        }

        return new JsonResponse($latLng->toLatLng());
    }
}
