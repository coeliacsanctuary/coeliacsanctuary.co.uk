<?php

declare(strict_types=1);

namespace App\Actions\Shop\TravelCardSearch;

use App\Ai\Agents\TravelCardSearchAgent;
use Illuminate\Support\Collection;
use Throwable;

class TravelCardSearchAiLookupAction
{
    /** @return Collection<int, non-empty-string> */
    public function handle(string $searchTerm): Collection
    {
        try {
            return (new TravelCardSearchAgent())->lookup($searchTerm);
        } catch (Throwable) {
            return collect();
        }
    }
}
