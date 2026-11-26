<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryMagicRoutes\Pages;

use App\Actions\EatingOut\MagicRouting\GenerateNewMagicRouteAction;
use App\Enums\EatingOut\EateryMagicRouteType;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\EateryMagicRouteResource;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\EatingOut\EateryTown;
use Filament\Resources\Pages\CreateRecord;

class CreateEateryMagicRoute extends CreateRecord
{
    protected static string $resource = EateryMagicRouteResource::class;

    /** @param array{resolver_type: EateryMagicRouteType|string, location_type: class-string<EateryCounty|EateryTown|EateryArea>, location_id: int|string} $data */
    protected function handleRecordCreation(array $data): EateryMagicRouteRecord
    {
        /** @var EateryCounty|EateryTown|EateryArea $location */
        $location = $data['location_type']::query()->withoutGlobalScopes()->findOrFail($data['location_id']);

        return app(GenerateNewMagicRouteAction::class)->handle(
            $data['resolver_type'] instanceof EateryMagicRouteType ? $data['resolver_type'] : EateryMagicRouteType::from($data['resolver_type']),
            $location,
        );
    }
}
