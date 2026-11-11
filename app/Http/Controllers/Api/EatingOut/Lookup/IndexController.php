<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Lookup;

use App\Actions\EatingOut\AutocompleteLocationAction;
use App\Http\Requests\EatingOut\Api\LookupRequest;
use Illuminate\Http\JsonResponse;

class IndexController
{
    public function __invoke(LookupRequest $request, AutocompleteLocationAction $autocompleteLocationAction): JsonResponse
    {
        return new JsonResponse([
            'data' => $autocompleteLocationAction->handle(
                $request->string('term')->toString(),
                $request->string('sessionToken')->toString(),
            ),
        ]);
    }
}
