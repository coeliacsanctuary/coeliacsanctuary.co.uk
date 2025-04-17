<?php

declare(strict_types=1);

namespace App\Actions\Shop\TravelCardSearch;

use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SearchTravelCardCountyOrLanguageAction
{
    public function handle(string $searchString): Collection
    {
        return TravelCardSearchTerm::query()
            ->where('term', 'like', "%{$searchString}%")
            ->get()
            ->map(fn (TravelCardSearchTerm $searchTerm) => [
                'id' => $searchTerm->id,
                'term' => Str::replace(
                    $searchString,
                    "<strong>{$searchString}</strong>",
                    $searchTerm->term,
                ),
                'type' => $searchTerm->type,
            ]);
    }
}
