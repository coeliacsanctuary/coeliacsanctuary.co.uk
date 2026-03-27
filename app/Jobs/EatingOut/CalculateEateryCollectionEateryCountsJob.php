<?php

declare(strict_types=1);

namespace App\Jobs\EatingOut;

use App\Models\EatingOut\EateryCollection;
use App\Services\EatingOut\Collection\Builder\BranchQueryBuilder;
use App\Services\EatingOut\Collection\Builder\EateryQueryBuilder;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class CalculateEateryCollectionEateryCountsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected EateryCollection $collection)
    {
    }

    public function handle(): void
    {
        try {
            $eateries = DB::select(new EateryQueryBuilder($this->collection->configuration)->toSql());
            $branches = DB::select(new BranchQueryBuilder($this->collection->configuration)->toSql());

            $count = count($eateries) + count($branches);

            $this->collection->update(['eateries_count' => $count]);
        } catch (Exception $e) {
            //
        }
    }
}
