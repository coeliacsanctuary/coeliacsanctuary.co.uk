<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Shop\CloseBasketAction;
use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CloseBasketsCommand extends Command
{
    protected $signature = 'coeliac:close-baskets';

    public function handle(): void
    {
        ShopOrder::query()
            ->where('state_id', OrderState::BASKET)
            ->where('updated_at', '<', Carbon::now()->subHour())
            ->get()
            ->each(function (ShopOrder $order): void {
                app(CloseBasketAction::class)->handle($order);
            });
    }
}
