<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\County\Town;

use App\Actions\EatingOut\GetFiltersForEateriesAction;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Http\Response\Inertia;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesPipeline;
use App\Resources\EatingOut\TownPageResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Response;

class ShowController
{
    public function __invoke(
        Request $request,
        EateryCounty $county,
        EateryTown $town,
        Inertia $inertia,
        GetFiltersForEateriesAction $getFiltersForTown,
        GetEateriesPipeline $getEateriesPipeline,
        GetEatingOutOpenGraphImageAction $getOpenGraphImageAction,
    ): Response {
        /** @var array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null, county: string | int | null }  $filters */
        $filters = [
            'categories' => $request->has('filter.category') ? explode(',', $request->string('filter.category')->toString()) : null,
            'venueTypes' => $request->has('filter.venueType') ? explode(',', $request->string('filter.venueType')->toString()) : null,
            'features' => $request->has('filter.feature') ? explode(',', $request->string('filter.feature')->toString()) : null,
        ];

        $county->load(['country']);
        $town->setRelation('county', $county);

        return $inertia
            ->title("Gluten Free Places to Eat in {$town->town}, {$county->county}")
            ->metaDescription("Coeliac Sanctuary gluten free places in {$town->town}, {$county->county} | Places can cater to Coeliac and Gluten Free diets in {$town->town}, {$county->county}!")
            ->metaTags($town->keywords())
            ->metaImage($getOpenGraphImageAction->handle($town))
            ->render('EatingOut/Town', [
                'town' => fn () => new TownPageResource($town),
                'eateries' => fn () => $getEateriesPipeline->run($town, $filters),
                'filters' => fn () => $getFiltersForTown->handle(
                    function (Builder $query) use ($town) {
                        /** @var Builder<Eatery> $query */
                        return $query
                            ->where('town_id', $town->id)
                            ->orWhereHas('nationwideBranches', fn (Builder $query) => $query->where('town_id', $town->id)); /** @phpstan-ignore-line */
                    },
                    $filters,
                ),
            ]);
    }
}
