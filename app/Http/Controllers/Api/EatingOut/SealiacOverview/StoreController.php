<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\SealiacOverview;

use App\Http\Requests\EatingOut\Api\SealiacOverviewRatingStoreRequest;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\SealiacOverview;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StoreController
{
    public function __invoke(Eatery $eatery, SealiacOverviewRatingStoreRequest $request): Response
    {
        try {
            SealiacOverview::query()
                ->where('wheretoeat_id', $eatery->id)
                ->where('nationwide_branch_id', $request->query('branchId'))
                ->where('invalidated', false)
                ->limit(1)
                ->increment($request->string('rating')->toString() === 'up' ? 'thumbs_up' : 'thumbs_down');

            return response()->noContent();
        } catch (Exception $e) {
            Log::error('Sealiac Rating failed', [
                'message' => $e->getMessage(),
                'eateryId' => $eatery->id,
                'branchId' => $request->query('branchId'),
                'trace' => $e->getTraceAsString(),
            ]);

            abort(404);
        }
    }
}
