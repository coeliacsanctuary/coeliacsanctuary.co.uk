<?php

namespace App\Nova\Actions\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

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
