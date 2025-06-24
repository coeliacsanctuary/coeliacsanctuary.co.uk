<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Details\Branches;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class IndexController
{
    public function __invoke(Request $request, Eatery $eatery): array
    {
        if ($eatery->county_id !== 1) {
            abort(404);
        }

        return [
            'data' => $eatery->nationwideBranches()
                ->with(['eatery', 'area', 'area.town', 'town', 'county'])
                ->where(
                    fn (Builder $query) => $query
                        ->whereLike('name', "%{$request->string('term')->toString()}%")
                        ->orWhereRelation('town', 'town', 'like', "%{$request->string('term')->toString()}%")
                )
                ->where('live', true)
                ->get()
                ->map(fn (NationwideBranch $branch) => [
                    'name' => $branch->short_name,
                ]),
        ];
    }
}
