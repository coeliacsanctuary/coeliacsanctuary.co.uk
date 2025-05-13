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
        $payload = [];

        if ($request->has('lat') && $request->has('lng')) {
            $payload['location'] = [
                'latitude' => $request->float('lat'),
                'longitude' => $request->float('lng'),
            ];
        }

        return Http::getAddress()
            ->post("/autocomplete/{$request->string('search')->toString()}", $payload)
            ->collect('suggestions')
            ->map(fn (array $result) => Arr::only($result, ['id', 'address']));
    }
}
