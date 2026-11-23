<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use Database\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class EateryScaffoldingSeeder extends Seeder
{
    public function run(): void
    {
        Factory::factoryForModel(EateryCountry::class)->create(['id' => 1, 'country' => 'England']);
        Factory::factoryForModel(EateryCounty::class)->create(['id' => 1, 'county' => 'Cheshire', 'country_id' => 1]);
        Factory::factoryForModel(EateryTown::class)->create(['id' => 1, 'town' => 'Crewe', 'county_id' => 1]);

        Factory::factoryForModel(EateryType::class)->create(['id' => 1, 'type' => 'wte', 'name' => 'Eatery']);
        Factory::factoryForModel(EateryType::class)->create(['id' => 2, 'type' => 'att', 'name' => 'Attraction']);
        Factory::factoryForModel(EateryType::class)->create(['id' => 3, 'type' => 'hotel', 'name' => 'Hotel / B&B']);

        Factory::factoryForModel(EateryVenueType::class)->count(5)
            ->sequence(fn (Sequence $sequence): array => ['id' => $sequence->index + 1])
            ->create();

        Factory::factoryForModel(EateryCuisine::class)->count(5)
            ->sequence(fn (Sequence $sequence): array => ['id' => $sequence->index + 1])
            ->create();

        Factory::factoryForModel(EateryFeature::class)->count(5)->create();
    }
}
