<?php

declare(strict_types=1);

namespace App\Actions\Shop\MyTravelCards;

use App\Models\Shop\ShopCustomer;

class CheckIfCustomerCanAccessMyTravelCardsAction
{
    public function handle(ShopCustomer $customer): bool
    {
        return $customer->orders()->where('has_digital_items', true)->exists();
    }
}
