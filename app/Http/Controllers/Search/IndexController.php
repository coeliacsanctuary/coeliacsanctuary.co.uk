<?php

declare(strict_types=1);

namespace App\Http\Controllers\Search;

use App\Actions\Search\IdentifySearchAreasWithAiAction;
use App\DataObjects\Search\SearchParameters;
use App\Http\Requests\Search\SearchRequest;
use App\Http\Response\Inertia;
use App\Models\Search\Search;
use App\Pipelines\Search\PerformSearchPipeline;
use App\Support\State\Search\SearchState;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Inertia\Response;

class IndexController
{
    public function __invoke(
        Inertia $inertia,
        SearchRequest $request,
        IdentifySearchAreasWithAiAction $identifySearchAreasWithAiAction,
        PerformSearchPipeline $performSearchPipeline,
    ): Response {
        $parameters = SearchParameters::fromRequest($request);

        /** @var Search $search */
        $search = Search::query()
            ->with('aiResponse')
            ->firstOrCreate(['term' => $request->string('q')->toString()]);

        $search->touch();

        $search->history()->create([
            'lat' => $parameters->userLocation[0] ?? null,
            'lng' => $parameters->userLocation[1] ?? null,
        ]);

        $aiAssisted = false;

        if ( ! Str::contains(URL::previousPath(), '/search') && ! Str::contains(URL::previousPath(), 'wheretoeat/search')) {
            $searchAiResponse = $identifySearchAreasWithAiAction->handle($search);

            if ($searchAiResponse) {
                $parameters->blogs = $searchAiResponse->blogs >= 10;
                $parameters->recipes = $searchAiResponse->recipes >= 10;
                $parameters->eateries = $searchAiResponse->eatingOut >= 10;
                $parameters->shop = $searchAiResponse->shop >= 10;

                if ($searchAiResponse->location) {
                    $parameters->locationSearch = $searchAiResponse->location;
                }

                Paginator::queryStringResolver(fn () => $parameters->toRequest());

                $aiAssisted = true;
            }
        }

        $results = $performSearchPipeline->run($parameters);

        return $inertia
            ->title("{$request->string('q')->toString()} - Search Results")
            ->doNotTrack()
            ->render('Search/Index', [
                'parameters' => $parameters->toResponse(),
                'location' => $parameters->locationSearch,
                'searchedLatLng' => SearchState::$lat ? ['lat' => SearchState::$lat, 'lng' => SearchState::$lng] : null,
                'results' => Inertia::defer(fn () => $results),
                'hasEatery' => SearchState::$hasGeoSearched,
                'aiAssisted' => $aiAssisted,
            ]);
    }
}
