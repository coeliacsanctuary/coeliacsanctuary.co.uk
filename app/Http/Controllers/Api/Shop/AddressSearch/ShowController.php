<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Shop\AddressSearch;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class ShowController
{
    public function __invoke(string $id): array
    {
        $response = Http::idealPostcodes()
            ->get("/autocomplete/addresses/{$id}/gbr")
            ->json('result');

        return [
            'address_1' => Arr::get($response, 'line_1'),
            'address_2' => Arr::get($response, 'line_2'),
            'address_3' => Arr::get($response, 'line_3'),
            'town' => Arr::get($response, 'post_town'),
            'county' => Arr::get($response, 'county'),
            'postcode' => Arr::get($response, 'postcode'),
        ];
    }
}
