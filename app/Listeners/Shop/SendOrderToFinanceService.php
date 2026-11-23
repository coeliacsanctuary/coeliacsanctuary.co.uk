<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Actions\Shop\FinanceService\SendOrderToFinanceServiceAction;
use App\Events\Shop\OrderPaidEvent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SendOrderToFinanceService implements ShouldQueue
{
    public function handle(OrderPaidEvent $event): void
    {
        try {
            app(SendOrderToFinanceServiceAction::class)->handle($event->order);
        } catch (Exception $exception) {
            Log::error('Unable to send order to finance service', [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTrace(),
            ]);
        }
    }
}
