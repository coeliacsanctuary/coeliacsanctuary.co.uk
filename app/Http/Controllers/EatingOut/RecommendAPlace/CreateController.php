<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\RecommendAPlace;

use App\Actions\EatingOut\ComputeRecommendAPlaceBackLinkAction;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Enums\EatingOut\EateryType;
use App\Http\Response\Inertia;
use App\Models\EatingOut\EateryVenueType;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Response;

class CreateController
{
    public function __invoke(Inertia $inertia, GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction, ComputeRecommendAPlaceBackLinkAction $computeRecommendAPlaceBackLinkAction): Response
    {
        $venueTypes = EateryVenueType::query()
            ->orderBy('venue_type')
            ->get()
            ->groupBy('type_id')
            ->map(fn (Collection $options, int $typeId) => [
                'label' => Str::of(EateryType::from($typeId)->name)->title()->plural()->toString(),
                'options' => $options
                    ->map(fn (EateryVenueType $eateryVenueType) => [
                        'label' => $eateryVenueType->venue_type,
                        'value' => $eateryVenueType->id,
                    ]),
            ])
            ->values();

        [$name, $previous] = $computeRecommendAPlaceBackLinkAction->handle();

        return $inertia
            ->title('Recommend A Place')
            ->doNotTrack()
            ->metaDescription('Recommend a place to be added to the Coeliac Sanctuary gluten free where to eat guide')
            ->metaImage($getOpenGraphImageForRouteAction->handle('eatery'))
            ->render('EatingOut/RecommendAPlace', [
                'venueTypes' => $venueTypes,
                'previous' => $previous,
                'name' => $name,
            ]);
    }
}
