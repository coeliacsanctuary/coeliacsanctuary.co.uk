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

class FindEateryMagicRouteRecordAction
{
    public function handle(EateryMagicRouteType $type, string $rawLocation, ?callable $withConfiguration = null): EateryMagicRouteRecord
    {
        $record = EateryMagicRouteRecord::query()
            ->where('resolver_type', $type)
            ->where('raw_location', $rawLocation)
            ->first();

        if ($record) {
            return $record;
        }

        // in pratice we'll never generate a fallback route record on the fly, but only as an admin action
        // this will later get extracted

        $location = app(ResolveLocationFromStringAction::class)->handle($rawLocation);

        if ( ! $location) {
            throw new RuntimeException('Location not found');
        }

        $key = match ($location::class) {
            EateryCounty::class => 'county_id',
            EateryTown::class => 'town_id',
            EateryArea::class => 'area_id',
        };

        $where = new Where("[parent].{$key}", '=', $location->id);

        $configuration = app(Configuration::class)
            ->addWhere($where);

        if ($withConfiguration) {
            $withConfiguration($configuration);
        }

        return EateryMagicRouteRecord::query()->create([
            'resolver_type' => $type,
            'raw_location' => $rawLocation,
            'location_type' => $location::class,
            'location_id' => $location->id,
            'builder_config' => $configuration,
        ]);
    }
}
