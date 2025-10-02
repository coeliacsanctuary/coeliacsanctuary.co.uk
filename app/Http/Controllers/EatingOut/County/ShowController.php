<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\County;

use App\Actions\EatingOut\GetMostRatedPlacesInCountyAction;
use App\Actions\EatingOut\GetTopRatedPlacesInCountyAction;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\EatingOut\EateryCounty;
use App\Resources\EatingOut\CountyPageResource;
use Inertia\Response;

class ShowController
{
    public function __invoke(
        EateryCounty $county,
        Inertia $inertia,
        GetMostRatedPlacesInCountyAction $getMostRatedPlacesInCounty,
        GetTopRatedPlacesInCountyAction $getTopRatedPlacesInCounty,
        GetEatingOutOpenGraphImageAction $getOpenGraphImageAction,
    ): Response {
        return $inertia
            ->title("Gluten Free Places to Eat in {$county->county}")
            ->metaDescription("Eateries who can cater to Coeliac and Gluten Free diets in {$county->county} | Gluten free places to eat in {$county->county}")
            ->metaTags($county->keywords())
            ->metaImage($getOpenGraphImageAction->handle($county))
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Eating Out', route('eating-out.index')),
                new BreadcrumbItemData($county->county),
            ]))
            ->render('EatingOut/County', [
                'county' => new CountyPageResource($county),
                'topRated' => fn () => $getTopRatedPlacesInCounty->handle($county),
                'mostRated' => fn () => $getMostRatedPlacesInCounty->handle($county),
            ]);
    }
}
