<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\County\Town;

use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\DataObjects\BreadcrumbItemData;
use App\DataObjects\EatingOut\PendingEatery;
use App\Http\Response\Inertia;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesPipeline;
use App\Resources\EatingOut\TownPageResource;
use App\Schema\PendingEaterySchema;
use App\Services\EatingOut\Filters\GetFiltersForTown;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Response;

class ShowController
{
    public function __invoke(
        Request $request,
        EateryCounty $county,
        EateryTown $town,
        Inertia $inertia,
        GetFiltersForTown $getFiltersForTown,
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

        $pipeline = $getEateriesPipeline->run($town, $filters);

        /** @var Collection<int, PendingEatery> $eateries */
        $eateries = $getEateriesPipeline->rawData()->eateries;

        return $inertia
            ->title("Gluten Free Places to Eat in {$town->town}, {$county->county}")
            ->metaDescription("Coeliac Sanctuary gluten free places in {$town->town}, {$county->county} | Places can cater to Coeliac and Gluten Free diets in {$town->town}, {$county->county}!")
            ->metaTags($town->keywords())
            ->metaImage($getOpenGraphImageAction->handle($town))
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Eating Out', route('eating-out.index')),
                new BreadcrumbItemData($county->county, route('eating-out.county', $county)),
                new BreadcrumbItemData($town->town),
            ]))
            ->schema(PendingEaterySchema::make($eateries, $town->town)->toScript())
            ->render('EatingOut/Town', [
                'town' => fn () => new TownPageResource($town),
                'eateries' => $pipeline,
                'filters' => fn () => $getFiltersForTown->setTown($town)->handle($filters),
            ]);
    }
}
