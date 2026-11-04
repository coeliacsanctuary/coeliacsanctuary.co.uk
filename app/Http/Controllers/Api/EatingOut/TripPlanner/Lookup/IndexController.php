<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\TripPlanner\Lookup;

use App\Actions\EatingOut\TripPlanner\AutocompleteLocationAction;
use App\Http\Requests\EatingOut\Api\TripPlannerLookupRequest;
use Illuminate\Http\JsonResponse;

class IndexController
{
    public function __invoke(TripPlannerLookupRequest $request, AutocompleteLocationAction $autocompleteLocationAction): JsonResponse
    {
        return new JsonResponse([
            'data' => $autocompleteLocationAction->handle(
                $request->string('term')->toString(),
                $request->string('sessionToken')->toString(),
            ),
        ]);
    }
}
