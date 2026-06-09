<?php

declare(strict_types=1);

namespace App\Actions\Shop\TravelCardSearch;

use App\Ai\Agents\TravelCardSearchAgent;
use Illuminate\Support\Collection;
use Throwable;

class TravelCardSearchAiLookupAction
{
    /** @return Collection<int, array{id: int, term: string, type: string}> */
    public function handle(string $searchTerm): ?Collection
    {
        try {
            $result = (new TravelCardSearchAgent())->lookup($searchTerm);

            if ($result) {
                return app(SearchTravelCardCountyOrLanguageAction::class)->handle($result);
            }
        } catch (Throwable) {
            //
        }

        return collect();
    }
}
