<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\TripPlanner;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Http\Requests\EatingOut\TripPlannerIndexRequest;
use App\Http\Response\Inertia;
use App\Models\OpenGraphImage;
use Illuminate\Support\Facades\URL;
use Inertia\Response;

class IndexController
{
    public function __invoke(TripPlannerIndexRequest $request, Inertia $inertia, GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction): Response
    {
        $ogImage = $getOpenGraphImageForRouteAction->handle('eatery-trip-planner', function (string $url, OpenGraphImage $image) {
            $timestamp = $image->updated_at->timestamp;

            return URL::query($url, ['cache' => $timestamp]);
        });

        return $inertia
            ->title('Gluten Free Trip Planner')
            ->metaDescription('Plan your journey and find places to eat that cater to Coeliac and gluten free diets along the way')
            ->metaTags(['gluten free trip planner', 'gluten free road trip', 'gluten free places to eat', 'eating out uk'])
            ->metaImage($ogImage)
            ->render('EatingOut/TripPlanner', [
                'destination' => $request->destination(),
            ]);
    }
}
