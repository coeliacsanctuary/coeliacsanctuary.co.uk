<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\CheckRecommendedPlace\Steps;

use App\DataObjects\EatingOut\RecommendAPlaceExistsCheckData;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckIfNationwideEatery extends AbstractStepAction
{
    protected function check(RecommendAPlaceExistsCheckData $data): void
    {
        if ( ! $data->name) {
            return;
        }

        $name = str_replace("'", '', $data->name);

        $eatery = Eatery::query()
            ->nationwide()
            ->where(DB::raw('replace(name, "\'", "")'), 'like', "%{$name}%")
            ->with('nationwideBranches')
            ->first();

        if ( ! $eatery) {
            return;
        }

        if ($data->location && $eatery->nationwideBranches->isNotEmpty()) {
            /** @var Collection<int, string> $locations */
            $locations = Str::explode($data->location);

            /** @var Collection<int, string> $locations */
            $locations = $locations->map(fn (string $line) => mb_trim($line, ',.-'))
                ->filter()
                ->values();

            $towns = EateryTown::query()
                ->where(function (Builder $query) use ($locations): void {
                    $locations->each(
                        fn (string $location) => $query
                            ->orWhere('town', 'like', "%{$location}%")
                    );
                })
                ->pluck('id');

            /** @var NationwideBranch|null $branch */
            $branch = $eatery->nationwideBranches()
                ->whereIn('town_id', $towns)
                ->orWhere(function (Builder|HasMany $query) use ($locations): void {
                    $locations->each(
                        fn (string $location) => $query
                            ->orWhere('address', 'like', "%{$location}%")
                    );
                })
                ->first();

            if ($branch) {
                $branch->load(['country', 'county', 'town']);

                $name = $branch->name !== '' ? $branch->name : $eatery->name;

                $data->found = true;
                $data->reason = "It looks like you're recommending {$name}, {$branch->short_location}, but we've already got it listed!";
                $data->url = $branch->link();
                $data->label = "Check out {$name}";
                $data->id = $eatery->id;
                $data->branchId = $branch->id;

                return;
            }
        }

        $data->found = true;
        $data->reason = "It looks like you're recommending {$eatery->name}, did you know they're already listed on our Nationwide Eateries page?";
        $data->url = route('eating-out.nationwide.show', ['eatery' => $eatery->slug]);
        $data->label = "Check out {$eatery->name}";
        $data->id = $eatery->id;
    }
}
