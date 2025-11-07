<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\Branches\Summary;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;

class IndexController
{
    public function __invoke(Eatery $eatery): array
    {
        if ($eatery->county_id !== 1) {
            abort(404);
        }

        $branches = $eatery->nationwideBranches()
            ->with(['eatery', 'area', 'area.town', 'town', 'county'])
            ->where('live', true)
            ->get()
            ->map(fn (NationwideBranch $branch) => [
                'name' => "{$branch->short_name} - {$branch->eateryPostcode()}",
            ]);

        return ['data' => $branches];
    }
}
