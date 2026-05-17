<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Ai\Agents\SealiacProductOverviewAgent;
use App\Models\SealiacOverview;
use App\Models\Shop\ShopProduct;
use Exception;

class GetSealiacProductOverviewAction
{
    public function handle(ShopProduct $product): SealiacOverview
    {
        if ($product->sealiacOverview) {
            return $product->sealiacOverview;
        }

        if ($product->reviews()->count() === 0) {
            throw new Exception('No reviews found to generate overview');
        }

        $response = (new SealiacProductOverviewAgent($product))->prompt('Generate your overview.');

        return SealiacOverview::query()->create([
            'model_id' => $product->id,
            'model_type' => ShopProduct::class,
            'overview' => $response->text,
        ]);
    }
}
