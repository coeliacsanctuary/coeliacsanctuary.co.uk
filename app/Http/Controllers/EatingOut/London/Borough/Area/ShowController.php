<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\London\Borough\Area;

use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Http\Response\Inertia;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesInLondonAreaPipeline;
use App\Resources\EatingOut\LondonAreaPageResource;
use App\Services\EatingOut\Filters\GetFiltersForLondonArea;
use Illuminate\Http\Request;
use Inertia\Response;

class ShowController
{
    public function __invoke(
        Request $request,
        EateryTown $borough,
        EateryArea $area,
        Inertia $inertia,
        GetFiltersForLondonArea $getFiltersForLondonArea,
        GetEateriesInLondonAreaPipeline $getEateriesPipeline,
        GetEatingOutOpenGraphImageAction $getOpenGraphImageAction,
    ): Response {
        if ($area->town_id !== $borough->id) {
            abort(404);
        }

        /** @var EateryCounty $county */
        $county = EateryCounty::query()->where('slug', 'london')->first();

        if ($borough->county_id !== $county->id) {
            abort(404);
        }

        $area->town->setRelation('county', $county);

        /** @var array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null, county: string | int | null }  $filters */
        $filters = [
            'categories' => $request->has('filter.category') ? explode(',', $request->string('filter.category')->toString()) : null,
            'venueTypes' => $request->has('filter.venueType') ? explode(',', $request->string('filter.venueType')->toString()) : null,
            'features' => $request->has('filter.feature') ? explode(',', $request->string('filter.feature')->toString()) : null,
        ];

        $otherAreas = EateryArea::query()
            ->whereNot('id', $area->id)
            ->with(['town', 'town.county'])
            ->where('area', $area->area)
            ->withCount(['liveEateries', 'liveBranches'])
            ->get()
            ->map(fn(EateryArea $alternateArea) => [
                'borough' => $alternateArea->town->town,
                'link' => $alternateArea->link(),
                'locations' => $alternateArea->live_eateries_count + $alternateArea->live_branches_count,
            ])
            ->sortBy('borough')
            ->values();

        return $inertia
            ->title("Gluten Free Places to Eat in {$area->area}, {$borough->town}, London")
            ->metaDescription("Coeliac Sanctuary gluten free places in the {$area->area}, {$borough->town} London | Places can cater to Coeliac and Gluten Free diets in {$borough->town}, {$county->county}!")
            ->metaTags(array_unique(array_merge($area->keywords(), $borough->keywords())))
//            ->metaImage($getOpenGraphImageAction->handle($town))
            ->render('EatingOut/LondonArea', [
                'area' => fn () => new LondonAreaPageResource($area),
                'alternateAreas' => fn () => $otherAreas,
                'eateries' => fn () => $getEateriesPipeline->run($area, $filters),
                'filters' => fn () => $getFiltersForLondonArea->setArea($area)->handle($filters),
            ]);
    }
}
