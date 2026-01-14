<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\LatLng;

use App\Http\Requests\EatingOut\Api\LatLngSearchRequest;
use App\Services\EatingOut\LocationSearchService;

class GetController
{
    public function __invoke(LatLngSearchRequest $request): array
    {
        $result = app(LocationSearchService::class)->getLatLng($request->string('term')->toString());

        return $result->toLatLng();
    }
}
