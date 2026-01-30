<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\EatingOut\CheckSingleEateryWebsiteJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAlert;
use App\Models\EatingOut\EateryCheck;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class ProcessEateryWebsiteChecksCommand extends Command
{
    protected int $batchSize = 150;

    protected int $intervalDays = 30;

    protected $signature = 'coeliac:process-eatery-website-checks';

    public function handle(): void
    {
        Eatery::query()
            ->whereNotNull('website')
            ->where('website', '!=', '')
            ->where('closed_down', false)
            ->where('county_id', '!=', 1)
            ->whereDoesntHave(
                'alerts',
                fn (Builder $query) => $query
                /** @var Builder<EateryAlert> $query */
                    ->where('completed', false)
                    ->where('ignored', false)
                    ->where('type', 'website')
            )
            ->where(
                fn (Builder $query) => $query
                    ->whereDoesntHave('check')
                    ->orWhereHas('check', fn (Builder $query) => $query
                        /** @var Builder<EateryCheck> $query */
                        ->whereNull('website_checked_at')
                        ->orWhere('website_checked_at', '<', now()->subDays($this->intervalDays)))
            )
            ->leftJoin('wheretoeat_checks', 'wheretoeat.id', '=', 'wheretoeat_checks.wheretoeat_id')
            ->orderByRaw('COALESCE(wheretoeat_checks.website_checked_at, "1970-01-01") ASC, wheretoeat.id ASC')
            ->select('wheretoeat.*')
            ->limit($this->batchSize)
            ->get()
            ->each(function (Eatery $eatery): void {
                CheckSingleEateryWebsiteJob::dispatch($eatery);
            });
    }
}
