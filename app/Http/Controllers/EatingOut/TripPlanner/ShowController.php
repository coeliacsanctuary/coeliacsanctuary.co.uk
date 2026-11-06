<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\GetTripStopsAction;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Http\Response\Inertia;
use App\Models\EatingOut\EateryTrip;
use App\Models\OpenGraphImage;
use App\Pipelines\EatingOut\GetEateries\TripEateriesPipeline;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Inertia\Response;

class ShowController
{
    public function __invoke(EateryTrip $eateryTrip, Request $request, Inertia $inertia, TripEateriesPipeline $tripEateriesPipeline, GetTripStopsAction $getTripStopsAction, GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction): Response
    {
        $ogImage = $getOpenGraphImageForRouteAction->handle('eatery-trip-planner', function (string $url, OpenGraphImage $image) {
            $timestamp = $image->updated_at->timestamp;

            return URL::query($url, ['cache' => $timestamp]);
        });

        $eateryTrip->recordVisit();

        /** @var string | null $token */
        $token = $request->cookie("trip-planner-{$eateryTrip->key}");

        /** @var array{categories: string[] | null, features: string[] | null, venueTypes: string[] | null} $filters */
        $filters = [
            'categories' => $request->filled('categories') ? $request->string('categories')->explode(',')->all() : null,
            'venueTypes' => $request->filled('venueTypes') ? $request->string('venueTypes')->explode(',')->all() : null,
            'features' => $request->filled('features') ? $request->string('features')->explode(',')->all() : null,
        ];

        $start = Arr::first($eateryTrip->points)['label'] ?? '';
        $end = Arr::last($eateryTrip->points)['label'] ?? '';

        return $inertia
            ->title("{$start} to {$end} - Trip Planner")
            ->metaDescription("Places to eat that cater to Coeliac and gluten free diets on the way from {$start} to {$end}")
            ->metaImage($ogImage)
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
                    filters: $filters,
                ),
            ]);
    }
}
