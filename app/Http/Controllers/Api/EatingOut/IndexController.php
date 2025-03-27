<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut;

use App\Models\EatingOut\EateryVenueType;
use App\Pipelines\EatingOut\GetEateries\GetFilteredEateriesPipeline;
use App\Resources\EatingOut\EateryAppResource;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class IndexController
{
    public function __invoke(Request $request, GetFilteredEateriesPipeline $getFilteredEateriesPipeline): array
    {
        $filters = [];

        if ($request->has('search') && json_validate($request->string('search')->toString())) {
            /** @var array<string, mixed> $params */
            $params = json_decode($request->string('search')->toString(), true);

            $filters['search'] = Arr::get($params, 'term');
            $filters['range'] = Arr::get($params, 'range');
            $filters['lat'] = Arr::get($params, 'lat');
            $filters['lng'] = Arr::get($params, 'lng');
        }

        if ($request->has('filter')) {
            $requestFilters = $request->collect('filter');

            if ($requestFilters->has('venueType')) {
                $filters['venueTypes'] = EateryVenueType::query()
                    ->whereIn('id', Str::of($requestFilters->get('venueType'))->explode(','))
                    ->get()
                    ?->pluck('slug')
                    ->toArray();
            }

            if ($requestFilters->has('county')) {
                $filters['county'] = $requestFilters->get('county');
            }
        }


        return [
            'data' => $getFilteredEateriesPipeline->run($filters, EateryAppResource::class), // @phpstan-ignore-line
        ];
    }
}
