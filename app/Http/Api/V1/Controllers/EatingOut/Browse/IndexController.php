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
        return [
            'data' => $browseEateriesPipeline->run($request->latLng(), $request->filters(), EateryBrowseResource::class),
        ];
    }
}
