<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\London\Borough;

use Illuminate\Support\Collection;
use App\Actions\EatingOut\GetMostRatedPlacesInTownAction;
use App\Actions\EatingOut\GetTopRatedPlacesInTownAction;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Resources\EatingOut\LondonBoroughPageResource;
use App\Resources\EatingOut\MagicRouteGuideResource;
use App\Resources\EatingOut\NearbyBoroughResource;
use Illuminate\Database\Eloquent\Relations\Relation;
use Inertia\Response;

class ShowController
{
    public function __invoke(
        EateryTown $borough,
        Inertia $inertia,
        GetTopRatedPlacesInTownAction $getTopRatedPlacesInTown,
        GetMostRatedPlacesInTownAction $getMostRatedPlacesInTown,
        GetEatingOutOpenGraphImageAction $getOpenGraphImageAction,
    ): Response {
        /** @var EateryCounty $county */
        $county = EateryCounty::query()->where('slug', 'london')->first();

        abort_if($borough->county_id !== $county->id, 404);

        $borough
            ->load(['areas' => fn (Relation $relation) => $relation->chaperone()->with(['liveEateries', 'liveBranches'])->orderBy('area')])
            ->setRelation('county', $county);

        return $inertia
            ->title("Gluten Free Places to Eat in the London borough of {$borough->town}")
            ->metaDescription("Coeliac Sanctuary gluten free places in the London borough of {$borough->town} | Places can cater to Coeliac and Gluten Free diets in {$borough->town}, {$county->county}!")
            ->metaTags($borough->keywords())
            ->metaImage($getOpenGraphImageAction->handle($borough))
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Eating Out', route('eating-out.index')),
                new BreadcrumbItemData('London', route('eating-out.london')),
                new BreadcrumbItemData($borough->town),
            ]))
            ->render('EatingOut/LondonBorough', [
                'borough' => fn (): LondonBoroughPageResource => new LondonBoroughPageResource($borough),
                'topRated' => fn (): Collection => $getTopRatedPlacesInTown->handle($borough),
                'mostRated' => fn (): Collection => $getMostRatedPlacesInTown->handle($borough),
                'nearby' => fn () => NearbyBoroughResource::collection($borough->nearbyTowns()),
                'guides' => fn () => MagicRouteGuideResource::collection($borough->magicRoutes),
            ]);
    }
}
