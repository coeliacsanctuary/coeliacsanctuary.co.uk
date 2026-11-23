<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;

class ResolveBasketAction
{
    public function handle(?string $token = null, bool $create = true): ?ShopOrder
    {
        if ($token) {
            $basket = ShopOrder::query()
                ->where('token', $token)
                ->where('state_id', OrderState::BASKET)
                ->first();

            if ($basket) {
                return $basket;
            }
        }

        if ($create) {
            return ShopOrder::query()->create();
        }

        return null;
    }
}
