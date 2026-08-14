<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\Search;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\DataObjects\EatingOut\LatLng;
use App\Http\Response\Inertia;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EaterySearchTerm;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\GetEateries\GetSearchResultsPipeline;
use App\Resources\EatingOut\EateryListResource;
use App\Services\EatingOut\Filters\GetFiltersForSearchResults;
use App\Support\Helpers;
use App\Support\State\EatingOut\Search\LatLngState;
use Illuminate\Http\Request;
use Inertia\Response;

class ShowController
{
    public function __invoke(
        Request $request,
        EaterySearchTerm $eaterySearchTerm,
        Inertia $inertia,
        GetSearchResultsPipeline $getSearchResultsPipeline,
        GetFiltersForSearchResults $getFiltersForSearchResults,
        GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction,
    ): Response {
        /** @var array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null, county: string | int | null } $filters */
        $filters = [
            'categories' => $request->has('filter.category') ? explode(',', $request->string('filter.category')->toString()) : null,
            'venueTypes' => $request->has('filter.venueType') ? explode(',', $request->string('filter.venueType')->toString()) : null,
            'features' => $request->has('filter.feature') ? explode(',', $request->string('filter.feature')->toString()) : null,
        ];

        $sort = $request->string('sort', 'distance')->toString();

        $eateries = $getSearchResultsPipeline->run($eaterySearchTerm, $filters, $sort);

        /** @var EateryListResource | null $jsonResource */
        $jsonResource = $eateries->collect()->first();

        /** @var Eatery|null $firstResult */
        $firstResult = $jsonResource?->resource;

        /** @var Eatery|NationwideBranch|null $firstLocation */
        $firstLocation = $firstResult?->relationLoaded('branch') ? $firstResult->branch : $firstResult;

        $firstLocation?->loadMissing(['town.county']);

        $locationFound = LatLngState::$latLng !== null;

        /** @var ?LatLng $latLng */
        $latLng = LatLngState::$latLng;

        if ( ! $latLng && $firstLocation) {
            $latLng = new LatLng($firstLocation->lat, $firstLocation->lng);
        }

        $formattedTerm = Helpers::formatSearchTerm($eaterySearchTerm->term);
        $displayTerm = $eaterySearchTerm->from_user_location ? 'your location' : $formattedTerm;

        $relatedPage = null;
        $county = EateryCounty::query()->where('county', $eaterySearchTerm->term)->first();

        if ($county) {
            $relatedPage = [
                'name' => $county->county,
                'link' => $county->link(),
            ];
        }

        if ( ! $relatedPage && $firstLocation?->town) {
            $relatedPage = [
                'name' => $firstLocation->town->town,
                'link' => $firstLocation->town->link(),
            ];
        }

        return $inertia
            ->title("{$displayTerm} - Search Results")
            ->metaImage($getOpenGraphImageForRouteAction->handle('eatery'))
            ->doNotTrack()
            ->render('EatingOut/SearchResults', [
                'term' => fn () => $displayTerm,
                'prefillTerm' => fn () => $eaterySearchTerm->from_user_location ? '' : $formattedTerm,
                'range' => fn () => $eaterySearchTerm->range,
                'eateries' => $inertia->scroll(fn () => $eateries),
                'filters' => fn () => $getFiltersForSearchResults->handle($filters),
                'latlng' => fn () => $latLng?->toLatLng(),
                'locationFound' => fn () => $locationFound,
                'relatedPage' => fn () => $relatedPage,
                'sort' => [
                    'current' => $sort,
                    'options' => [
                        [
                            'label' => 'Alphabetical',
                            'value' => 'alphabetical',
                        ],
                        [
                            'label' => 'Top Rated',
                            'value' => 'rating',
                        ],
                        [
                            'label' => 'Distance',
                            'value' => 'distance',
                        ],
                    ],
                ],
            ]);
    }
}
