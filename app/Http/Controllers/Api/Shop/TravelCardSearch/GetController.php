<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Shop\TravelCardSearch;

use App\Models\Shop\ShopProduct;
use App\Models\Shop\TravelCardSearchTerm;
use App\Resources\Shop\ShopProductIndexResource;

class GetController
{
    /** @return array{term: string, type: string, products: mixed} */
    public function __invoke(TravelCardSearchTerm $travelCardSearchTerm): array
    {
        $travelCardSearchTerm->increment('hits');

        $products = $travelCardSearchTerm->products
            ->load(['reviews', 'prices', 'variants', 'media'])
            /** @phpstan-ignore-next-line */
            ->filter(fn (ShopProduct $product) => $product->currentPrice !== null)
            ->values();

        return [
            'term' => $travelCardSearchTerm->display_term,
            'type' => $travelCardSearchTerm->type,
            'products' => ShopProductIndexResource::collection($products),
        ];
    }
}
