<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\SealiacOverview\Feedback;

use App\Http\Api\V1\Requests\EatingOut\SealiacOverviewFeedbackRequest;
use App\Models\EatingOut\Eatery;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class StoreController
{
    public function __invoke(Eatery $eatery, SealiacOverviewFeedbackRequest $request): Response
    {
        abort_if(!$eatery->sealiacOverview, Response::HTTP_NOT_FOUND);

        try {
            $eatery->sealiacOverview?->increment($request->string('rating')->toString() === 'up' ? 'thumbs_up' : 'thumbs_down');

            return response()->noContent();
        } catch (Throwable $e) {
            Log::error('Sealiac Rating failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            abort(404);
        }
    }
}
