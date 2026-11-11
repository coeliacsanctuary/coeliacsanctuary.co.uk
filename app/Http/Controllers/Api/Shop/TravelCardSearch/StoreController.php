<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\SuggestTravelCardSearchTermsAction;
use App\Http\Requests\Shop\TravelCardSearchRequest;
use App\Models\Shop\TravelCardSearchTermHistory;
use Illuminate\Support\Collection;

class StoreController
{
    /** @return array{data: Collection<int, array{id: int|null, term: string, value: string, type: string}>} */
    public function __invoke(
        TravelCardSearchRequest $request,
        SuggestTravelCardSearchTermsAction $suggestTravelCardSearchTermsAction,
    ): array {
        $searchString = mb_trim($request->string('term')->toString());

        TravelCardSearchTermHistory::query()
            ->firstOrCreate(['term' => $searchString], ['hits' => 0])
            ->increment('hits');

        return ['data' => $suggestTravelCardSearchTermsAction->handle($searchString)];
    }
}
