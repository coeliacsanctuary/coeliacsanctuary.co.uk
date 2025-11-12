<?php

declare(strict_types=1);

namespace App\Pipelines\EatingOut\CheckRecommendedPlace\Steps;

use App\DataObjects\EatingOut\RecommendAPlaceExistsCheckData;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryTown;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckIfStandardEatery extends AbstractStepAction
{
    protected function check(RecommendAPlaceExistsCheckData $data): void
    {
        if ( ! $data->name || ! $data->location) {
            return;
        }

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

        $name = str_replace("'", '', $data->name);

        $eatery = Eatery::query()
            ->notNationwide()
            ->where(DB::raw('replace(name, "\'", "")'), 'like', "%{$name}%")
            ->where(function (Builder $builder) use ($towns, $locations) {
                /** @var Builder<Eatery> $builder */
                return $builder
                    ->whereIn('town_id', $towns)
                    ->orWhere(function (Builder $query) use ($locations): void {
                        $locations->each(
                            fn (string $location) => $query
                                ->orWhere('address', 'like', "%{$location}%")
                        );
                    });
            })
            ->with(['town', 'county'])
            ->first();

        if ($eatery) {
            $data->found = true;
            $data->reason = "It looks like you're recommending {$eatery->name}, {$eatery->short_location}, did you know they're already listed in our eating out guide?";
            $data->url = $eatery->link();
            $data->label = "Check out {$eatery->name}";
            $data->id = $eatery->id;
        }
    }
}
