<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\TripPlanner\Lookup;

use App\Actions\EatingOut\TripPlanner\GetPlaceLocationAction;
use App\Http\Requests\EatingOut\Api\TripPlannerLookupShowRequest;
use Illuminate\Http\JsonResponse;
use RuntimeException;

class ShowController
{
    public function __invoke(string $placeId, TripPlannerLookupShowRequest $request, GetPlaceLocationAction $getPlaceLocationAction): JsonResponse
    {
        try {
            $latLng = $getPlaceLocationAction->handle($placeId, $request->string('sessionToken')->toString());
        } catch (RuntimeException) {
            abort(404);
        }

        return new JsonResponse($latLng->toLatLng());
    }
}
