<?php

declare(strict_types=1);

namespace App\Filament\Support;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Database\Eloquent\Builder;

class FindEateryOrBranch
{
    /** @return array<string, string> */
    public function handle(string $search): array
    {
        if (empty($search)) {
            return [];
        }

        $withoutScopes = fn ($query) => $query->withoutGlobalScopes();

        $eateries = Eatery::query()
            ->with(['area' => $withoutScopes, 'town' => $withoutScopes, 'county' => $withoutScopes, 'country'])
            ->whereLike('name', "%{$search}%")
            ->get();

        $branches = NationwideBranch::query()
            ->with(['area' => $withoutScopes, 'town' => $withoutScopes, 'county' => $withoutScopes, 'country', 'eatery'])
            ->whereHas('eatery')
            ->where(function (Builder $query) use ($search): void {
                $query
                    ->whereLike('name', "%{$search}%")
                    ->orWhere(function (Builder $query) use ($search): void {
                        $query
                            ->where('name', '')
                            ->whereHas('town', fn (Builder $town) => $town->withoutGlobalScopes()->whereLike('town', "%{$search}%"));
                    });
            })
            ->get();

        return $eateries
            ->toBase()
            ->merge($branches)
            ->sortBy(fn (Eatery|NationwideBranch $location): string => $location->full_name)
            ->mapWithKeys(fn (Eatery|NationwideBranch $location): array => [
                $location instanceof NationwideBranch ? "{$location->wheretoeat_id}:{$location->id}" : "{$location->id}:" => $location->full_name,
            ])
            ->all();
    }

    public function label(string $value): ?string
    {
        [$eateryId, $branchId] = explode(':', $value);

        $withoutScopes = fn ($query) => $query->withoutGlobalScopes();

        if ($branchId) {
            return NationwideBranch::query()
                ->with(['area' => $withoutScopes, 'town' => $withoutScopes, 'county' => $withoutScopes, 'country', 'eatery'])
                ->whereHas('eatery')
                ->find($branchId)
                ?->full_name;
        }

        return Eatery::query()
            ->with(['area' => $withoutScopes, 'town' => $withoutScopes, 'county' => $withoutScopes, 'country'])
            ->find($eateryId)
            ?->full_name;
    }
}
