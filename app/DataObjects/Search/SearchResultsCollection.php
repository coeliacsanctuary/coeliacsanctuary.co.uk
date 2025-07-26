<?php

declare(strict_types=1);

namespace App\DataObjects\Search;

use Illuminate\Support\Collection;

class SearchResultsCollection
{
    /**
     * @param  Collection<int, SearchResultItem>  $blogs
     * @param  Collection<int, SearchResultItem>  $recipes
     * @param  Collection<int, SearchResultItem>  $eateries
     * @param  Collection<int, SearchResultItem>  $shop
     */
    public function __construct(
        public readonly Collection $blogs = new Collection(),
        public readonly Collection $recipes = new Collection(),
        public readonly Collection $eateries = new Collection(),
        public readonly Collection $shop = new Collection(),
    ) {
        //
    }
}
