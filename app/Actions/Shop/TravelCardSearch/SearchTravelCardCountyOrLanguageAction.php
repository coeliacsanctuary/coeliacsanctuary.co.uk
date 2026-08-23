<?php

declare(strict_types=1);

namespace App\Actions\Shop\TravelCardSearch;

use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Support\Collection;

class SearchTravelCardCountyOrLanguageAction
{
    /** @return Collection<int, array{id: int, term: string, value: string, type: string}> */
    public function handle(string $searchString): Collection
    {
        $searchString = mb_trim($searchString);

        if ($searchString === '') {
            return collect();
        }

        return TravelCardSearchTerm::query()
            ->matching($searchString)
            ->get()
            ->map(fn (TravelCardSearchTerm $searchTerm) => [
                'id' => $searchTerm->id,
                'term' => $this->highlight($searchTerm->display_term, $searchString),
                'value' => $searchTerm->display_term,
                'type' => $searchTerm->type,
            ]);
    }

    protected function highlight(string $term, string $searchString): string
    {
        return (string) preg_replace(
            '/(' . preg_quote($searchString, '/') . ')/i',
            '<strong>$1</strong>',
            $term,
        );
    }
}
