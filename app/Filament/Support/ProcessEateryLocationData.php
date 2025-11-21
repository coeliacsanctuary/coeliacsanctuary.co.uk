<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;

class ProcessEateryLocationData
{
    public static function handle(array $data): array
    {
        if ( ! isset($data['country_id']) && isset($data['country'])) {
            $country = EateryCountry::query()->firstOrCreate(['country' => $data['country']]);

            $data['country_id'] = $country->id;
        }

        if ( ! isset($data['county_id']) && isset($data['county'])) {
            $county = EateryCounty::query()->firstOrCreate(['county' => $data['county'], 'country_id' => $data['country_id']]);

            $data['county_id'] = $county->id;
        }

        if ( ! isset($data['town_id']) && isset($data['town'])) {
            $town = EateryTown::query()->firstOrCreate(['town' => $data['town'], 'county_id' => $data['county_id']]);

            $data['town_id'] = $town->id;
        }

        if ($data['county'] === 'London' && ! isset($data['area_id']) && isset($data['area'])) {
            $area = EateryArea::query()->firstOrCreate(['area' => $data['area'], 'town_id' => $data['town_id']]);

            $data['area_id'] = $area->id;
        }

        unset($data['country'], $data['county'], $data['town'], $data['area']);

        return $data;
    }
}
