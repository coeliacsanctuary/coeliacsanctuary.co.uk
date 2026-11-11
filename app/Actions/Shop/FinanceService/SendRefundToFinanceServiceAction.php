<?php

declare(strict_types=1);

namespace App\Actions\Shop\FinanceService;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPaymentRefund;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SendRefundToFinanceServiceAction
{
    public function handle(ShopPaymentRefund $refund): void
    {
        /** @var ShopOrder $order */
        $order = $refund->order;

        $payload = [
            'refund_id' => (string) $refund->id,
            'refunded_at' => $refund->created_at->toIso8601String(),
            'stripe_refund_id' => $refund->refund_id,
            'amount' => $refund->amount,
            'reason' => Str::limit($refund->note, 252),
        ];

        Http::financeService()->post("/api/v1/orders/{$order->order_key}/refunds", $payload);
    }
}
