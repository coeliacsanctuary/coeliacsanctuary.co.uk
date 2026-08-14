<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Browse;

use App\Http\Requests\EatingOut\Api\EatingOutBrowseRequest;
use App\Pipelines\EatingOut\GetEateries\BrowseEateriesPipeline;

class IndexController
{
    public function __invoke(EatingOutBrowseRequest $request, BrowseEateriesPipeline $browseEateriesPipeline): array
    {
        return [
            'data' => $browseEateriesPipeline->run($request->latLng(), $request->filters(), isMeters: true),
        ];
    }
}
