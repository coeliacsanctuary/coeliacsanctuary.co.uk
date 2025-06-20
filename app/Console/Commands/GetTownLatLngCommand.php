<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EatingOut\EateryTown;
use App\Services\EatingOut\LocationSearchService;
use Illuminate\Console\Command;

class GetTownLatLngCommand extends Command
{
    protected $signature = 'one-time:coeliac:get-town-latlng';

    public function handle(LocationSearchService $locationSearchService): void
    {
        EateryTown::withoutGlobalScopes()
            ->with(['county', 'county.country'])
            ->where('town', '!=', 'Nationwide')
            ->whereNull('latlng')
            ->lazy()
            ->each(function (EateryTown $town) use ($locationSearchService): void {
                $name = "{$town->town}, {$town->county?->county}, {$town->county?->country?->country}";
                $latLng = $locationSearchService->getLatLng($name, force: true);

                $town->updateQuietly([
                    'latlng' => $latLng->toString(),
                ]);

                $this->info("Updated the latlng for {$name}");
            });
    }
}
