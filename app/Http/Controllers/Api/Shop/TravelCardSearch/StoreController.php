<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\SearchTravelCardCountyOrLanguageAction;
use App\Actions\Shop\TravelCardSearch\TravelCardSearchAiLookupAction;
use App\Http\Requests\Shop\TravelCardSearchRequest;
use App\Models\Shop\TravelCardSearchTermHistory;

class StoreController
{
    public function __invoke(TravelCardSearchRequest $request, SearchTravelCardCountyOrLanguageAction $searchTravelCardCountyOrLanguageAction, TravelCardSearchAiLookupAction $travelCardSearchAiLookup): array
    {
        $searchString = $request->string('term')->toString();

        TravelCardSearchTermHistory::query()
            ->firstOrCreate(['term' => $searchString], ['hits' => 0])
            ->increment('hits');

        $results = $searchTravelCardCountyOrLanguageAction->handle($searchString);

        if ($results->isEmpty()) {
            $results = $travelCardSearchAiLookup->handle($searchString);
        }

        return [
            'data' => $results,
        ];
    }
}
