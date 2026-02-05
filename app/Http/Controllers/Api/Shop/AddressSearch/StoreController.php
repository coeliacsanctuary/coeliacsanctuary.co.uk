<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Shop\AddressSearch;

use App\Http\Requests\Shop\AddressSearchRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class StoreController
{
    /** @return Collection<int, array{id: string, address: string}> */
    public function __invoke(AddressSearchRequest $request): Collection
    {
        $payload = [
            'query' => $request->string('search')->toString(),
        ];

        if ($request->has('lat') && $request->has('lng')) {
            $payload['bias_lonlat'] = "{$request->float('lng')},{$request->float('lat')},5000";
        }

        return Http::idealPostcodes()
            ->get('/autocomplete/addresses', $payload)
            ->collect('result.hits')
            ->map(fn (array $result) => [
                'id' => Arr::get($result, 'id'),
                'address' => Arr::get($result, 'suggestion'),
            ]);
    }
}
