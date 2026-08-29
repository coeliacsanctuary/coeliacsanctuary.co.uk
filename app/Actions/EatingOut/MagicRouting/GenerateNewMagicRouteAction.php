<?php

declare(strict_types=1);

namespace App\Actions\EatingOut\MagicRouting;

use App\Enums\EatingOut\EateryMagicRouteType;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\EatingOut\EateryTown;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Join;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Order;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use App\Services\EatingOut\Collection\Configuration;
use RuntimeException;

class GenerateNewMagicRouteAction
{
    public function handle(
        EateryMagicRouteType $eateryMagicRouteType,
        EateryCounty|EateryTown|EateryArea $location,
    ): EateryMagicRouteRecord {
        $key = match ($location::class) {
            EateryCounty::class => 'county_id',
            EateryTown::class => 'town_id',
            EateryArea::class => 'area_id',
            default => throw new RuntimeException('Unsupported location type'),
        };

        $configuration = app(Configuration::class);

        $configuration->addWhere(new Where("[parent].{$key}", '=', $location->id));

        if ($location instanceof EateryCounty) {
            $configuration->addJoin(new Join('wheretoeat_towns', 'wheretoeat_towns.id', '[parent].town_id'));
            $configuration->addOrder(new Order('wheretoeat_towns.town', 'asc'));
        }

        $configuration->addOrder(new Order('[parent].name', 'asc'));

        if ($eateryMagicRouteType->builderConfiguration()) {
            $eateryMagicRouteType->builderConfiguration()($configuration);
        }

        $routeRecord = EateryMagicRouteRecord::query()->make([
            'resolver_type' => $eateryMagicRouteType,
            'raw_location' => $location->slug,
            'location_type' => $location::class,
            'location_id' => $location->id,
            'builder_config' => $configuration,
        ]);

        $agent = $eateryMagicRouteType->agent([$routeRecord]);

        $routeRecord->body = json_decode($agent->prompt('Generate the content')->text, true);
        $routeRecord->save();

        return $routeRecord;
    }
}
