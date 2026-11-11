<?php

declare(strict_types=1);

namespace App\Builders\EatingOut;

use App\Concerns\EatingOut\QueriesNationwideEateries;
use App\DataObjects\EatingOut\LatLng;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Database\Eloquent\Builder;

/** @extends Builder<NationwideBranch> */
class NationwideBranchBuilder extends Builder
{
    use QueriesNationwideEateries;

    public function selectDistance(LatLng $latLng, array $columns = []): static
    {
        return $this->selectRaw('(
                        6371000 * acos (
                          cos ( radians(?) )
                          * cos( radians( wheretoeat_nationwide_branches.lat ) )
                          * cos( radians( wheretoeat_nationwide_branches.lng ) - radians(?) )
                          + sin ( radians(?) )
                          * sin( radians( wheretoeat_nationwide_branches.lat ) )
                        )
                     ) AS distance', [
            $latLng->lat,
            $latLng->lng,
            $latLng->lat,
        ])
            ->addSelect($columns);
    }
}
