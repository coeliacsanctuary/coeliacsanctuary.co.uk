<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Browse;

use App\Http\Api\V1\Requests\EatingOut\BrowseRequest;
use App\Http\Api\V1\Resources\EatingOut\EateryBrowseResource;
use App\Pipelines\EatingOut\GetEateries\BrowseEateriesPipeline;

class IndexController
{
    public function __invoke(BrowseRequest $request, BrowseEateriesPipeline $browseEateriesPipeline): array
    {
        /** @var array{categories: array<string>|null, features: array<string>|null, venueTypes: array<string>|null, county: int|string|null} $filters */
        $filters = $request->filters();

        return [
            'data' => $browseEateriesPipeline->run($request->latLng(), $filters, EateryBrowseResource::class),
        ];
    }
}
