<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\Nationwide;

use App\Actions\EatingOut\GetMostRatedPlacesInCountyAction;
use App\Actions\EatingOut\GetTopRatedPlacesInCountyAction;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\EatingOut\EateryCounty;
use App\Resources\EatingOut\NationwidePageResource;
use App\Services\EatingOut\Filters\GetFiltersForNationwide;
use Illuminate\Http\Request;
use Inertia\Response;

class IndexController
{
    public function __invoke(
        Request $request,
        Inertia $inertia,
        GetFiltersForNationwide $getFiltersForNationwide,
        GetMostRatedPlacesInCountyAction $getMostRatedPlacesInCounty,
        GetTopRatedPlacesInCountyAction $getTopRatedPlacesInCounty,
        GetEatingOutOpenGraphImageAction $getOpenGraphImageAction,
    ): Response {
        /** @var EateryCounty $county */
        $county = EateryCounty::query()->firstWhere('slug', 'nationwide');

        /** @var array{categories: string[] | null, venueTypes: string[] | null, features: string[] | null} $filters */
        $filters = [
            'categories' => $request->has('filter.category') ? explode(',', $request->string('filter.category')->toString()) : null,
            'venueTypes' => $request->has('filter.venueType') ? explode(',', $request->string('filter.venueType')->toString()) : null,
            'features' => $request->has('filter.feature') ? explode(',', $request->string('filter.feature')->toString()) : null,
        ];

        return $inertia
            ->title('Gluten Free Nationwide Chains')
            ->metaDescription('Nationwide chains across the UK that can cater to gluten free diets')
            ->metaTags([
                'coeliac nationwide chains', 'gluten free nationwide chains', 'gluten free food at nationwide chains',
                'gluten free places to eat at chains in the uk', ...$county->keywords(),
            ])
            ->metaImage($getOpenGraphImageAction->handle($county))
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Eating Out', route('eating-out.index')),
                new BreadcrumbItemData('Nationwide Chains'),
            ]))
            ->render('EatingOut/Nationwide', [
                'county' => fn () => (new NationwidePageResource($county))->withFilters($filters),
                'filters' => fn () => $getFiltersForNationwide->setCounty($county)->handle($filters),
                'topRated' => fn () => $getTopRatedPlacesInCounty->handle($county),
                'mostRated' => fn () => $getMostRatedPlacesInCounty->handle($county),
            ]);
    }
}
