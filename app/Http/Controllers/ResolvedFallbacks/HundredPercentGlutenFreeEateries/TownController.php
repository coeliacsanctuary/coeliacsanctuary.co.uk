<?php

declare(strict_types=1);

namespace App\Http\Controllers\ResolvedFallbacks\HundredPercentGlutenFreeEateries;

use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
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

class TownController
{
    use FormatsMarkdown;

    public function __invoke(
        Request $request,
        EateryMagicRouteRecord $routeRecord,
        GetEateriesForMagicRoutePipeline $getEateriesForMagicRoutePipeline,
        GetEatingOutOpenGraphImageAction $getOpenGraphImageAction,
        Inertia $inertia,
    ): Response {
        /** @var EateryTown $town */
        $town = $routeRecord->location;

        /** @var EateryCounty $county */
        $county = $town->county;

        /** @var Collection<int, Eatery> $eateries */
        $eateries = $getEateriesForMagicRoutePipeline->run($routeRecord->builder_config);

        return $inertia
            ->title("Eating 100% Gluten Free in {$town->town}")
            ->metaDescription(Arr::string($routeRecord->body, 'meta_description'))
            ->metaTags(Arr::array($routeRecord->body, 'meta_keywords'))
            ->metaImage($getOpenGraphImageAction->handle($town))
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Eating Out', route('eating-out.index')),
                new BreadcrumbItemData($county->county, route('eating-out.county', $county)),
                new BreadcrumbItemData($town->town, route('eating-out.town', [$county, $town])),
            ]))
            ->render('EatingOut/MagicRoutes/HundredPercentGlutenFree/Town', [
                'page_intro' => $this->formatMarkdown(Arr::string($routeRecord->body, 'page_intro')),
                'page_outro' => $this->formatMarkdown(Arr::string($routeRecord->body, 'outro')),
                'town' => [
                    'name' => $town->town,
                    'link' => $town->link(),
                    'latlng' => $town->latlng,
                    'image' => $town->image ?: $county->image,
                ],
                'eateries' => $eateries
                    ->map(function (Eatery $eatery) use ($routeRecord) {
                        $key = $eatery->slug . ($eatery->relationLoaded('branch') && $eatery->branch ? "-{$eatery->branch->id}" : '');

                        return [
                            'key' => $key,
                            'details' => EateryListResource::make($eatery),
                            'info' => $this->formatMarkdown(Arr::string($routeRecord->body, $key)),
                        ];
                    })
                    ->values(),
            ]);
    }
}
