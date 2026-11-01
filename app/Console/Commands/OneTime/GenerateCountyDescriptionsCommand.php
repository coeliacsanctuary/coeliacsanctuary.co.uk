<?php

declare(strict_types=1);

namespace App\Console\Commands\OneTime;

use Illuminate\Console\Attributes\Signature;
use App\Jobs\EatingOut\GenerateCountyDescriptionJob;
use App\Models\EatingOut\EateryCounty;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

#[Signature('coeliac:one-time:generate-county-descriptions {--force}')]
class GenerateCountyDescriptionsCommand extends Command
{
    public function handle(): void
    {
        EateryCounty::query()
            ->whereNot('county', 'Nationwide')
            ->when( ! $this->option('force'), fn (Builder $query) => $query->whereNull('description'))
            ->lazy()
            ->each(function (EateryCounty $county): void {
                $this->info("Queueing description for {$county->county}");

                GenerateCountyDescriptionJob::dispatch($county);
            });
    }
}
