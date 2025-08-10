<?php

declare(strict_types=1);

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        $eateries = [
            [
                'name' => 'Braye Chippy',
                'county' => 'Alderney',
                'town' => 'Braye',
            ],
            [
                'name' => 'Crabby Jacks',
                'county' => 'Guernsey',
                'town' => 'Albecq',
            ],
            [
                'name' => 'The Blonde Hedgehog',
                'county' => 'Guernsey',
                'town' => 'St Anne',
            ],
            [
                'name' => 'L\'Horizon Hotel',
                'county' => 'Jersey',
                'town' => 'St Brelade',
            ],
            [
                'name' => 'Jersey Zoo',
                'county' => 'Jersey',
                'town' => 'La Profunde Rue',
            ],
            [
                'name' => 'Brasserie At DeGruchy',
                'county' => 'Jersey',
                'town' => 'St Helier',
            ],
            [
                'name' => 'The Trinity Arms',
                'county' => 'Jersey',
                'town' => 'Trinity',
            ],
        ];

        /** @var EateryCountry $channelIslands */
        $channelIslands = EateryCountry::query()->where('country', 'Channel Islands')->first();

        foreach ($eateries as $eatery) {
            $model = Eatery::query()
                ->where('country_id', $channelIslands->id)
                ->where('name', $eatery['name'])
                ->sole();

            $county = EateryCounty::query()->firstOrCreate([
                'country_id' => $channelIslands->id,
                'county' => $eatery['county'],
            ]);

            $town = EateryTown::query()->firstOrCreate([
                'county_id' => $county->id,
                'town' => $eatery['town'],
            ]);

            $model->update([
                'county_id' => $county->id,
                'town_id' => $town->id,
            ]);
        }
    }
};
