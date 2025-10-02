<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\London;

use App\Actions\EatingOut\GetMostRatedPlacesInCountyAction;
use App\Actions\EatingOut\GetTopRatedPlacesInCountyAction;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\EatingOut\EateryCounty;
use App\Resources\EatingOut\LondonPageResource;
use Inertia\Response;

class IndexController
{
    public function __invoke(
        Inertia $inertia,
        GetMostRatedPlacesInCountyAction $getMostRatedPlacesInCounty,
        GetTopRatedPlacesInCountyAction $getTopRatedPlacesInCounty,
        GetEatingOutOpenGraphImageAction $getOpenGraphImageAction,
    ): Response {
        /** @var EateryCounty $county */
        $county = EateryCounty::query()->firstWhere('slug', 'london');

        return $inertia
            ->title('Eating Gluten Free in London')
            ->metaDescription('Discover the best gluten free places to eat in London, from dedicated coeliac-safe restaurants to hidden gems offering gluten free menus across the city.')
            ->metaTags([
                'coeliac london', 'gluten free london', 'eating gluten free in london',
                'gluten free places to eat at chains in london', ...$county->keywords(),
            ])
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Eating Out', route('eating-out.index')),
                new BreadcrumbItemData('London'),
            ]))
            ->metaImage($getOpenGraphImageAction->handle($county))
            ->render('EatingOut/London', [
                'london' => new LondonPageResource($county),
                'topRated' => fn () => $getMostRatedPlacesInCounty->handle($county),
                'mostRated' => fn () => $getTopRatedPlacesInCounty->handle($county),
            ]);
    }
}
