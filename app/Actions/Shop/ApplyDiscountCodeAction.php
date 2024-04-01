<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\DiscountCodeType;
use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopOrderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ApplyDiscountCodeAction
{
    public function handle(ShopDiscountCode $discountCode, string $basketToken): ?int
    {
        app(VerifyDiscountCodeAction::class)->handle($discountCode, $basketToken);

        if ($discountCode->type_id === DiscountCodeType::MONEY) {
            return $discountCode->deduction;
        }

        if ($discountCode->type_id === DiscountCodeType::PERCENTAGE) {
            $runningTotal = ShopOrderItem::query()
                ->whereRelation('order', fn (Builder $builder) => $builder
                    ->where('state_id', OrderState::BASKET)
                    ->where('token', $basketToken))
                ->sum(DB::raw('product_price * quantity'));

            return ($runningTotal / 100) * $discountCode->deduction;
        }
    }
}
