<?php

declare(strict_types=1);

namespace App\Console\Commands\OneTime;

use App\Jobs\EatingOut\GenerateCountyDescriptionJob;
use App\Models\EatingOut\EateryCounty;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class GenerateCountyDescriptionsCommand extends Command
{
    protected $signature = 'coeliac:one-time:generate-county-descriptions {--force}';

    public function handle(): void
    {
        EateryCounty::query()
            ->whereNot('county', 'Nationwide')
            ->when( ! $this->option('force'), fn (Builder $query) => $query->whereNull('description'))
            ->lazy()
            ->each(function (EateryCounty $county): void {
                $this->info("Queueing description for {$county->county}");

                GenerateCountyDescriptionJob::dispatchSync($county);
            });
    }
}
