<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\SealiacOverview;

use App\Actions\EatingOut\GetSealiacEateryOverviewAction;
use App\Actions\SealiacOverview\FormatResponseAction;
use App\Models\EatingOut\Eatery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetController
{
    public function __invoke(Eatery $eatery, Request $request, GetSealiacEateryOverviewAction $getSealiacEateryOverviewAction, FormatResponseAction $formatResponseAction): array
    {
        $branch = null;

        abort_if($eatery->closed_down, 404);

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
                    'overview' => $formatResponseAction->handle($sealiacOverview->overview),
                    'id' => $sealiacOverview->id,
                ],
            ];
        } catch (Exception $exception) {
            Log::error('Sealiac AI Overview failed', [
                'message' => $exception->getMessage(),
                'eateryId' => $eatery->id,
                'branchId' => $branch?->id,
                'trace' => $exception->getTraceAsString(),
            ]);

            abort(404);
        }
    }
}
