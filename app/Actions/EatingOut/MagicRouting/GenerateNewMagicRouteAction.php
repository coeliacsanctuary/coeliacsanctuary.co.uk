<?php

declare(strict_types=1);

namespace App\Actions\EatingOut\MagicRouting;

use App\Enums\EatingOut\EateryMagicRouteType;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\EatingOut\EateryTown;
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

        $where = new Where("[parent].{$key}", '=', $location->id);

        $configuration = app(Configuration::class)->addWhere($where);

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
