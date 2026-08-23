<?php

declare(strict_types=1);

namespace App\Actions\Shop\TravelCardSearch;

use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class MatchTravelCardSearchTermsAction
{
    /** @return EloquentCollection<int, TravelCardSearchTerm> */
    public function handle(string $searchString): EloquentCollection
    {
        $searchString = mb_trim($searchString);

        if ($searchString === '') {
            return new EloquentCollection();
        }

        $wholeStringMatch = $this->matchTerm($searchString);

        if ($wholeStringMatch instanceof TravelCardSearchTerm) {
            return new EloquentCollection([$wholeStringMatch]);
        }

        $parts = $this->splitTerm($searchString);

        if ($parts->count() < 2) {
            return new EloquentCollection();
        }

        $matched = $parts
            ->map(fn (string $part) => $this->matchTerm($part))
            ->filter(fn (?TravelCardSearchTerm $term) => $term instanceof TravelCardSearchTerm);

        if ($matched->count() !== $parts->count()) {
            return new EloquentCollection();
        }

        return new EloquentCollection($matched->unique('id')->values()->all());
    }

    /** @return Collection<int, non-empty-string> */
    protected function splitTerm(string $searchString): Collection
    {
        return Str::of($searchString)
            ->replaceMatches('/\s+(?:and|&|\+)\s+|\s*[\/,]\s*/i', '|')
            ->explode('|')
            ->map(fn (string $part) => mb_trim($part))
            ->filter(fn (string $part) => mb_strlen($part) > 0)
            ->values();
    }

    protected function matchTerm(string $part): ?TravelCardSearchTerm
    {
        $part = mb_trim($part);

        if ($part === '') {
            return null;
        }

        /** @var TravelCardSearchTerm|null $term */
        $term = TravelCardSearchTerm::query()->matching($part)->first();

        return $term;
    }
}
