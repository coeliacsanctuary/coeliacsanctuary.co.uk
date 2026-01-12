<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Lookup;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class GetController
{
    public function __invoke(string $id): JsonResponse
    {
        $response = Http::getAddress()->get("get-location/{$id}");

        if ( ! $response->successful()) {
            abort(404);
        }

        /** @var array{latitude: float, longitude: float} $location */
        $location = $response->json();

        return response()->json([
            'lat' => $location['latitude'],
            'lng' => $location['longitude'],
        ]);
    }
}
