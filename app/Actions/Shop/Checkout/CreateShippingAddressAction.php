<?php

declare(strict_types=1);

namespace App\Actions\Shop\Checkout;

use App\DataObjects\Shop\PendingOrderShippingAddressDetails;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopShippingAddress;

class CreateShippingAddressAction
{
    public function handle(ShopCustomer $customer, PendingOrderShippingAddressDetails $addressDetails): ShopShippingAddress
    {
        return $customer->addresses()->create([
            'name' => $customer->name,
            ...$addressDetails->toArray(),
        ]);
    }
}
