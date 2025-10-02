<?php

declare(strict_types=1);

namespace App\Nova\Actions\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Actions\Action;

class ResetPrintStatus extends Action
{
    use InteractsWithQueue;
    use Queueable;

    public function handle(): void
    {
        ShopOrder::query()
            ->where('state_id', OrderState::READY)
            ->update(['state_id' => OrderState::PAID]);
    }
}
