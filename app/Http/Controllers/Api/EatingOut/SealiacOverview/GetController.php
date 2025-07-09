<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\SealiacOverview;

use App\Actions\EatingOut\GetSealiacEateryOverviewAction;
use App\Models\EatingOut\Eatery;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GetController
{
    public function __invoke(Eatery $eatery, Request $request, GetSealiacEateryOverviewAction $getSealiacEateryOverviewAction): array
    {
        /*
        * todo - nova
        *
        * - add ai overviews resource
        * - add relation to eatery/branch resources
        * - add ability to manually invalidate an overview resource
        */

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
            return [
                'data' => Str::of($getSealiacEateryOverviewAction->handle($eatery, $branch))
                    ->markdown([
                        'renderer' => [
                            'soft_break' => '<br />',
                        ],
                    ])
                    ->replaceFirst('<p>', '<p><span class="quote-elem open"><span>&ldquo;</span></span>')
                    ->replaceLast('<p>', '<p><span class="quote-elem close"><span>&rdquo;</span></span>'),
            ];
        } catch (Exception $e) {
            Log::error('Sealiac AI Overview failed', [
                'message' => $e->getMessage(),
                'eateryId' => $eatery->id,
                'branchId' => $branch?->id,
                'trace' => $e->getTraceAsString(),
            ]);

            abort(404);
        }
    }
}
