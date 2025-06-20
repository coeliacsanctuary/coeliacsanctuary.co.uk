<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EatingOut\EateryArea;
use App\Services\EatingOut\LocationSearchService;
use Illuminate\Console\Command;

class GetAreaLatLngCommand extends Command
{
    protected $signature = 'one-time:coeliac:get-area-latlng';

    public function handle(LocationSearchService $locationSearchService): void
    {
        EateryArea::withoutGlobalScopes()
            ->with(['town', 'town.county', 'town.county.country'])
            ->whereNull('latlng')
            ->lazy()
            ->each(function (EateryArea $area) use ($locationSearchService): void {
                /** @phpstan-ignore-next-line  */
                $name = "{$area->area}, {$area->town->town}, {$area->town->county?->county}, {$area->town->county?->country?->country}";

                $latLng = $locationSearchService->getLatLng($name, force: true);

                $area->updateQuietly([
                    'latlng' => $latLng->toString(),
                ]);

                $this->info("Updated the latlng for {$name}");
            });
    }
}
