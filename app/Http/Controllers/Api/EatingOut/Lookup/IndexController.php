<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Lookup;

use App\Http\Requests\EatingOut\Api\LookupRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class IndexController
{
    public function __invoke(LookupRequest $request): JsonResponse
    {
        $response = Http::getAddress()->post("location/{$request->string('term')->toString()}", [
            'filter' => [
                'country' => ['UK', 'Ireland']
            ]
        ]);

        if ( ! $response->successful()) {
            return response()->json(['data' => []]);
        }

        /** @var array{location: string, id: string} $suggestions */
        $suggestions = $response->json('suggestions');

        /** @var Collection<int, array{location: string, id: string}> $collection */
        $collection = collect($suggestions);

        return response()->json([
            'data' => $collection->map(fn (array $location) => [
                'id' => $location['id'],
                'label' => $location['location'],
            ]),
        ]);
    }
}
