<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Money\Money;
use RuntimeException;

class VerifyDiscountCodeAction
{
    public function handle(ShopDiscountCode $discountCode, string $basketToken): void
    {
        if ($discountCode->start_at->isFuture() || $discountCode->end_at->isPast()) {
            throw new RuntimeException('This discount code is not currently active');
        }

        $discountCode->loadCount('used');

        if ($discountCode->max_claims > 0 && $discountCode->used_count >= $discountCode->max_claims) {
            throw new RuntimeException('Discount Code has had too many claims');
        }

        /** @var int $runningSubtotal */
        $runningSubtotal = ShopOrderItem::query()
            ->whereRelation('order', function (Builder $builder) use ($basketToken) {
                /** @var Builder<ShopOrder> $builder */
                return $builder
                    ->where('state_id', OrderState::BASKET)
                    ->where('token', $basketToken);
            })
            ->sum(DB::raw('product_price * quantity'));

        if ($runningSubtotal < $discountCode->min_spend) {
            $remaining = Helpers::formatMoney(Money::GBP($discountCode->min_spend - $runningSubtotal));

            throw new RuntimeException("Your basket doesnt meet the minimum value for this discount code, please add items worth {$remaining} to qualify.");
        }
    }
}
