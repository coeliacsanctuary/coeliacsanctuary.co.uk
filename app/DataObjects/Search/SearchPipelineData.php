<?php

declare(strict_types=1);

namespace App\DataObjects\Search;

class SearchPipelineData
{
    public function __construct(
        public readonly SearchParameters $parameters,
        public readonly SearchResultsCollection $results,
    ) {
        //
    }
}
