<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\EatingOut\Api\EatingOutBrowseSearchRequest;
use App\Services\EatingOut\LocationSearchService;
use Exception;
use Illuminate\Http\JsonResponse;

class EatingOutBrowseSearchController
{
    public function __invoke(EatingOutBrowseSearchRequest $request, LocationSearchService $locationSearchService): JsonResponse
    {
        try {
            $response = $locationSearchService->getLatLng($request->string('term')->toString());

            return new JsonResponse([
                'lat' => $response->lat,
                'lng' => $response->lng,
            ]);
        } catch (Exception) {
            return new JsonResponse(status: JsonResponse::HTTP_NOT_FOUND);
        }
    }
}
