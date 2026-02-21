<?php

declare(strict_types=1);

namespace App\Support\Ai\Prompts;

use App\Models\Shop\ShopProduct;

class ShopProductSealiacOverviewPrompt
{
    public function handle(ShopProduct $product): string
    {
        $product->loadMissing('reviews');

        return view(
            'prompts.sealiac-product-overview',
            ['product' => $product]
        )->render();
    }
}
