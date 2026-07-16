<?php

declare(strict_types=1);

namespace App\Http\Controllers\ResolvedFallbacks\HundredPercentGlutenFreeEateries;

use App\Actions\OpenGraphImages\GenerateCountyOpenGraphImageAction;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Ai\Agents\MagicRoutes\HundredPercentGlutenFreeCountyContentAgent;
use App\Concerns\FormatsMarkdown;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use App\Resources\EatingOut\EateryListResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Inertia\Response;

class CountyController
{
    use FormatsMarkdown;

    public function __invoke(
        Request $request,
        EateryMagicRouteRecord $routeRecord,
        GetEateriesForMagicRoutePipeline $getEateriesForMagicRoutePipeline,
        GetEatingOutOpenGraphImageAction $getOpenGraphImageAction,
        Inertia $inertia,
    ): Response {
        // move to action in future
        if ( ! $routeRecord->body) {
            $agent = HundredPercentGlutenFreeCountyContentAgent::make($routeRecord);

            $body = $agent->prompt('Generate the content');
            $routeRecord->update(['body' => $body]);
        }

        /** @var EateryCounty $county */
        $county = $routeRecord->location;

        /** @var Collection<int, Eatery> $eateries */
        $eateries = $getEateriesForMagicRoutePipeline->run($routeRecord->builder_config, []);

        return $inertia
            ->title("Eating 100% Gluten Free in {$county->county}")
            ->metaDescription(Arr::string($routeRecord->body, 'meta_description'))
            ->metaTags(Arr::array($routeRecord->body, 'meta_keywords'))
            ->metaImage($getOpenGraphImageAction->handle($county))
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Eating Out', route('eating-out.index')),
                new BreadcrumbItemData($county->county, route('eating-out.county', $county)),
            ]))
            ->render('EatingOut/MagicRoutes/HundredPercentGlutenFree/County', [
                'page_intro' => $this->formatMarkdown(Arr::string($routeRecord->body, 'page_intro')),
                'county' => [
                    'name' => $county->county,
                    'link' => $county->link(),
                    'latlng' => $county->latlng,
                    'image' => $county->image,
                ],
                'towns' => $eateries->groupBy(fn (Eatery $eatery) => $eatery->town_id)
                    ->map(function (Collection $townEateries) use ($routeRecord) {
                        /** @var Eatery $firstEatery */
                        $firstEatery = $townEateries->first();

                        /** @var EateryTown $town */
                        $town = $firstEatery->town;

                        return [
                            'slug' => $town->slug,
                            'name' => $town->town,
                            'link' => $town->link(),
                            'intro' => $this->formatMarkdown(Arr::string($routeRecord->body, $town->slug)),
                            'eateries' => $townEateries->map(fn (Eatery $eatery) => EateryListResource::make($eatery)),
                        ];
                    })
                    ->sortBy('name')
                    ->values(),
            ]);
    }
}
