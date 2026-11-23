<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\Nearby;

use App\Actions\EatingOut\GetNearbyEateriesAction;
use App\Models\EatingOut\Eatery;
use Illuminate\Http\Request;

class ShowController
{
    public function __invoke(Request $request, Eatery $eatery, GetNearbyEateriesAction $getNearbyEateriesAction): array
    {
        $branch = null;

        if ($request->has('branchId')) {
            $branch = $eatery->nationwideBranches()->findOrFail($request->integer('branchId'));
        }

        return [
            'data' => [
                'eateries' => $getNearbyEateriesAction->handle($branch ?? $eatery),
            ],
        ];
    }
}
