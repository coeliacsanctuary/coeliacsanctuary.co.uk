<?php

declare(strict_types=1);

namespace App\Actions\Shop\TravelCardSearch;

use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class SuggestTravelCardSearchTermsAction
{
    public function __construct(
        protected SearchTravelCardCountyOrLanguageAction $searchTravelCardCountyOrLanguage,
        protected MatchTravelCardSearchTermsAction $matchTravelCardSearchTerms,
        protected TravelCardSearchAiLookupAction $travelCardSearchAiLookup,
    ) {
    }

    /** @return Collection<int, array{id: int|null, term: string, value: string, type: string}> */
    public function handle(string $searchString): Collection
    {
        $results = $this->suggestions($searchString);

        if ($results->isNotEmpty()) {
            return $results;
        }

        $countries = $this->travelCardSearchAiLookup->handle($searchString);

        if ($countries->isEmpty()) {
            return collect();
        }

        return $this->suggestions($countries->join(' and '));
    }

    /** @return Collection<int, array{id: int|null, term: string, value: string, type: string}> */
    protected function suggestions(string $searchString): Collection
    {
        $results = $this->searchTravelCardCountyOrLanguage->handle($searchString);

        if ($results->isNotEmpty()) {
            return $results->map($this->row(...));
        }

        return $this->multiDestination($this->matchTravelCardSearchTerms->handle($searchString), $searchString);
    }

    /**
     * @param  array{id: int|null, term: string, value: string, type: string} $result
     * @return array{id: int|null, term: string, value: string, type: string}
     */
    protected function row(array $result): array
    {
        return [
            'id' => $result['id'],
            'term' => $result['term'],
            'value' => $result['value'],
            'type' => $result['type'],
        ];
    }

    /**
     * @param  EloquentCollection<int, TravelCardSearchTerm> $terms
     * @return Collection<int, array{id: int|null, term: string, value: string, type: string}>
     */
    protected function multiDestination(EloquentCollection $terms, string $searchString): Collection
    {
        if ($terms->count() < 2) {
            return collect();
        }

        /** @var Collection<int, string> $names */
        $names = $terms->map(fn (TravelCardSearchTerm $term) => $term->display_term);

        return collect([[
            'id' => null,
            'term' => $names->join(', ', ' and '),
            'value' => $searchString,
            'type' => "{$terms->count()} destinations",
        ]])->concat($terms->map(fn (TravelCardSearchTerm $term) => [
            'id' => $term->id,
            'term' => $term->display_term,
            'value' => $term->display_term,
            'type' => $term->type,
        ]))->values();
    }
}
