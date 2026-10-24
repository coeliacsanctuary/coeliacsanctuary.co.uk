<?php

declare(strict_types=1);

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Http\Requests\NovaRequest;

Route::post('/print', function (NovaRequest $request): void {
    $ids = $request->string('ids');

    ShopOrder::query()
        ->when(
            $ids->is('all'),
            fn (Builder $query) => $query->where('state_id', OrderState::PAID),
            fn (Builder $query) => $query->whereIn('id', $ids->explode(',')),
        )
        ->where('is_digital_only', false)
        ->where('state_id', '!=', OrderState::SHIPPED)
        ->each(fn (ShopOrder $order) => $order->update(['state_id' => OrderState::READY]));
});
