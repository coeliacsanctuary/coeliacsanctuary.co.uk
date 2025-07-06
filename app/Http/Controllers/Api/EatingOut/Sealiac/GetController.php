<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Sealiac;

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
         * todo - front end
         *
         * - add error handling, if error returned, emit event to Detail page, and hide the Card with v-if
         * - add large faded quote icons at start and end of overview text
         * - check these on large screens?
         * - add whats this button, open modal explaining
         * - add thumbs up/thumbs down rating section, send axios request then hide block
         *
         * todo - after front end
         *
         * - create endpoint(s)? to recieve thumbs up/thumbs down
         * - plus test
         *
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
                'data' => Str::markdown($getSealiacEateryOverviewAction->handle($eatery, $branch), [
                    'renderer' => [
                        'soft_break' => '<br />',
                    ],
                ]),
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
