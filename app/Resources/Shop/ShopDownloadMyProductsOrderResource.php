<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ShopOrder */
class ShopDownloadMyProductsOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'number' => $this->order_key,
            'date' => $this->payment->created_at->format('d/m/Y'),
            'name' => $this->customer->name,
        ];
    }
}
