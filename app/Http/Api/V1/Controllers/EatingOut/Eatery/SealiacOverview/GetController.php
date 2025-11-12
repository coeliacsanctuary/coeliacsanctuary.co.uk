<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\SealiacOverview;

use App\Actions\EatingOut\GetSealiacEateryOverviewAction;
use App\Models\EatingOut\Eatery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GetController
{
    public function __invoke(Request $request, Eatery $eatery, GetSealiacEateryOverviewAction $getSealiacEateryOverviewAction): array
    {
        $branch = null;

        if ($eatery->closed_down) {
            abort(404);
        }

        $eatery->load(['area', 'town', 'county', 'country']);

        if ($request->filled('branchId')) {
            $branch = $eatery->nationwideBranches()
                ->with(['area', 'town', 'county', 'country'])
                ->where('live', true)
                ->findOrFail($request->integer('branchId'));
        }

        try {
            $sealiacOverview = $getSealiacEateryOverviewAction->handle($eatery, $branch);

            return [
                'data' => [
                    'overview' => Str::explode($sealiacOverview->overview, "\n\n"),
                    'id' => $sealiacOverview->id,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Sealiac AI Overview failed in api/v1', [
                'message' => $e->getMessage(),
                'eateryId' => $eatery->id,
                'branchId' => $branch?->id,
                'trace' => $e->getTraceAsString(),
            ]);

            abort(404);
        }
    }
}
