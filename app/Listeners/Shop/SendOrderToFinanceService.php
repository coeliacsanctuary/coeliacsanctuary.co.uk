<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Actions\Shop\FinanceService\SendOrderToFinanceServiceAction;
use App\Events\Shop\OrderPaidEvent;
use Exception;
use Illuminate\Support\Facades\Log;

class SendOrderToFinanceService
{
    public function handle(OrderPaidEvent $event): void
    {
        try {
            app(SendOrderToFinanceServiceAction::class)->handle($event->order);
        } catch (Exception $e) {
            Log::error('Unable to send order to finance service', [
                'message' => $e->getMessage(),
                'trace' => $e->getTrace(),
            ]);
        }
    }
}
