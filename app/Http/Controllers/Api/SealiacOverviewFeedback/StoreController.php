<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\SealiacOverviewFeedback;

use App\Http\Requests\Api\SealiacOverviewFeedback\StoreRequest;
use App\Models\EatingOut\Eatery;
use App\Models\SealiacOverview;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StoreController
{
    public function __invoke(SealiacOverview $sealiacOverview, StoreRequest $request): Response
    {
        try {
            $sealiacOverview->increment($request->string('rating')->toString() === 'up' ? 'thumbs_up' : 'thumbs_down');

            return response()->noContent();
        } catch (Exception $e) {
            Log::error('Sealiac Rating failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            abort(404);
        }
    }
}
