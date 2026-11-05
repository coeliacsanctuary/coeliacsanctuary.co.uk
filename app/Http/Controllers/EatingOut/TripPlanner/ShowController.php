<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\GetTripStopsAction;
use App\Http\Response\Inertia;
use App\Models\EatingOut\EateryTrip;
use App\Pipelines\EatingOut\GetEateries\TripEateriesPipeline;
use App\Resources\EatingOut\EateryTripResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Inertia\Response;

class ShowController
{
    public function __invoke(EateryTrip $eateryTrip, Request $request, Inertia $inertia, TripEateriesPipeline $tripEateriesPipeline, GetTripStopsAction $getTripStopsAction): Response
    {
        $eateryTrip->recordVisit();

        /** @var string | null $token */
        $token = $request->cookie("trip-planner-{$eateryTrip->key}");

        $start = Arr::first($eateryTrip->points)['label'] ?? '';
        $end = Arr::last($eateryTrip->points)['label'] ?? '';

        return $inertia
            ->title("{$start} to {$end} - Trip Planner")
            ->metaDescription("Places to eat that cater to Coeliac and gluten free diets on the way from {$start} to {$end}")
            ->doNotTrack()
            ->render('EatingOut/TripPlanner', [
                'trip' => [
                    'key' => $eateryTrip->key,
                    'points' => $eateryTrip->points,
                    'distance' => $eateryTrip->distance,
                    'stopInterval' => $eateryTrip->stop_interval,
                    'route' => [
                        'geometry' => $eateryTrip->geometry,
                        'distance' => $eateryTrip->route_distance,
                        'duration' => $eateryTrip->route_duration,
                    ],
                ],
                'stops' => fn (): array => $getTripStopsAction->handle($eateryTrip->toTripRoute(), $eateryTrip->stop_interval),
                'canEdit' => $eateryTrip->canBeEditedWith($token),
                'eateries' => fn (): Collection => $tripEateriesPipeline->run(
                    route: $eateryTrip->toTripRoute(),
                    radius: $eateryTrip->distance,
                    filters: ['categories' => null, 'features' => null, 'venueTypes' => null],
                    jsonResource: EateryTripResource::class,
                ),
            ]);
    }
}
