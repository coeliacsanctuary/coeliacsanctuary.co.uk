<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Features;

use App\Services\EatingOut\Filters\GetFilters;
use Illuminate\Http\Request;

class IndexController
{
    public function __invoke(Request $request, GetFilters $getFiltersForTown): array
    {
        /** @var array{categories: string[], features: string[], venueTypes: string []}  $filters */
        $filters = [
            'categories' => $request->has('filter.category') ? explode(',', $request->string('filter.category')->toString()) : null,
            'venueTypes' => $request->has('filter.venueType') ? explode(',', $request->string('filter.venueType')->toString()) : null,
            'features' => $request->has('filter.feature') ? explode(',', $request->string('filter.feature')->toString()) : null,
        ];

        return [
            'data' => $getFiltersForTown->handle($filters),
        ];
    }
}
