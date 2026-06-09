<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPayment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ShopOrder */
class ShopDownloadMyProductsOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ShopPayment $payment */
        $payment = $this->payment;

        /** @var ShopCustomer $customer */
        $customer = $this->customer;

        return [
            'number' => $this->order_key,
            'date' => $payment->created_at?->format('d/m/Y'),
            'name' => $customer->name,
        ];
    }
}
