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
        return EateryMagicRouteRecord::query()
            ->where('resolver_type', $type)
            ->where('raw_location', $rawLocation)
            ->firstOrFail();
    }
}
