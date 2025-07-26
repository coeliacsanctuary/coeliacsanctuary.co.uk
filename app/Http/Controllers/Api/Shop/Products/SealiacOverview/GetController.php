<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Shop\Products\SealiacOverview;

use App\Actions\SealiacOverview\FormatResponseAction;
use App\Actions\Shop\GetSealiacProductOverviewAction;
use App\Models\Shop\ShopProduct;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GetController
{
    public function __invoke(ShopProduct $product, Request $request, GetSealiacProductOverviewAction $getSealiacProductOverviewAction, FormatResponseAction $formatResponseAction): array
    {
        try {
            $sealiacOverview = $getSealiacProductOverviewAction->handle($product);

            return [
                'data' => [
                    'overview' => $formatResponseAction->handle($sealiacOverview->overview),
                    'id' => $sealiacOverview->id,
                ],
            ];
        } catch (Exception $e) {
            Log::error('Sealiac AI Overview failed', [
                'message' => $e->getMessage(),
                'productId' => $product->id,
                'trace' => $e->getTraceAsString(),
            ]);

            abort(404);
        }
    }
}
