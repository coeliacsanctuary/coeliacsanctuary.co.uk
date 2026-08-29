<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Shop\CloseBasketAction;
use App\Actions\Shop\GetPaymentIntentAction;
use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;

class ClosePendingOrdersCommand extends Command
{
    protected $signature = 'coeliac:close-pending-orders';

    public function handle(): void
    {
        /** @var int $limit */
        $limit = config('coeliac.shop.pending_order_timeout_minutes');

        ShopOrder::query()
            ->where('state_id', OrderState::PENDING)
            ->where('updated_at', '<', Carbon::now()->subMinutes($limit))
            ->get()
            ->each(function (ShopOrder $order): void {
                if ($this->hasBeenPaidFor($order)) {
                    Log::critical('Pending order has a succeeded payment intent', [
                        'order_id' => $order->id,
                        'payment_intent_id' => $order->payment_intent_id,
                    ]);

                    return;
                }

                app(CloseBasketAction::class)->handle($order);
            });
    }

    protected function hasBeenPaidFor(ShopOrder $order): bool
    {
        if ( ! $order->payment_intent_id) {
            return false;
        }

        try {
            $intent = app(GetPaymentIntentAction::class)->handle($order->payment_intent_id);

            return $intent->status === PaymentIntent::STATUS_SUCCEEDED;
        } catch (Exception $exception) {
            Log::error('Unable to check payment intent for pending order - ' . $exception->getMessage(), [
                'order_id' => $order->id,
            ]);

            return true;
        }
    }
}
