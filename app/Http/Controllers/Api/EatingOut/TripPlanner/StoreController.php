<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\GetRouteAction;
use App\Http\Requests\EatingOut\Api\TripPlannerRequest;
use App\Pipelines\EatingOut\GetEateries\TripEateriesPipeline;
use App\Resources\EatingOut\EateryTripResource;
use Illuminate\Http\JsonResponse;

class StoreController
{
    public function __invoke(TripPlannerRequest $request, GetRouteAction $getRouteAction, TripEateriesPipeline $tripEateriesPipeline): JsonResponse
    {
        $route = $getRouteAction->handle($request->points());

        return new JsonResponse([
            'route' => [
                'geometry' => $route->geometry,
                'distance' => $route->distance,
                'duration' => $route->duration,
            ],
            'eateries' => $tripEateriesPipeline->run(
                route: $route,
                radius: $request->float('distance'),
                filters: ['categories' => null, 'features' => null, 'venueTypes' => null],
                jsonResource: EateryTripResource::class,
            ),
        ]);
    }
}
