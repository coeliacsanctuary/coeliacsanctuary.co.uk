<?php

declare(strict_types=1);

namespace App\Builders\Shop;

use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<TravelCardSearchTerm> */
class TravelCardSearchTermBuilder extends Builder
{
    public function matching(string $term): static
    {
        return $this
            ->where('term', 'like', '%' . addcslashes($term, '%_\\') . '%')
            ->orderByRaw('term = ? desc', [$term])
            ->orderByRaw('locate(?, term) asc', [$term])
            ->orderByRaw('char_length(term) asc');
    }
}
