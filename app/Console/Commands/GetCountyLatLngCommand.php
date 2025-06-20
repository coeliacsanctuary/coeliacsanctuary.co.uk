<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\EatingOut\EateryCounty;
use App\Services\EatingOut\LocationSearchService;
use Illuminate\Console\Command;

class GetCountyLatLngCommand extends Command
{
    protected $signature = 'one-time:coeliac:get-county-latlng';

    public function handle(LocationSearchService $locationSearchService): void
    {
        EateryCounty::withoutGlobalScopes()
            ->with('country')
            ->where('county', '!=', 'Nationwide')
            ->whereNull('latlng')
            ->lazy()
            ->each(function (EateryCounty $county) use ($locationSearchService): void {
                $latLng = $locationSearchService->getLatLng("{$county->county}, {$county->country?->country}", force: true);

                $county->updateQuietly([
                    'latlng' => $latLng->toString(),
                ]);

                $this->info("Updated the latlng for {$county->county}, {$county->country?->country}");
            });
    }
}
