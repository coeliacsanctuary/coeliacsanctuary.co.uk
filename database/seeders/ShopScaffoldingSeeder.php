<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Shop\DiscountCodeType;
use App\Enums\Shop\OrderState;
use App\Enums\Shop\PaymentType;
use App\Enums\Shop\PostageArea;
use App\Enums\Shop\ShippingMethod;
use App\Models\Shop\ShopDiscountCodeType;
use App\Models\Shop\ShopOrderState;
use App\Models\Shop\ShopPaymentType;
use App\Models\Shop\ShopPostageCountry;
use App\Models\Shop\ShopPostageCountryArea;
use App\Models\Shop\ShopShippingMethod;
use Illuminate\Database\Seeder;

class ShopScaffoldingSeeder extends Seeder
{
    public function run(): void
    {
        foreach (DiscountCodeType::cases() as $discountCodeType) {
            ShopDiscountCodeType::query()->create([
                'id' => $discountCodeType->value,
                'name' => $discountCodeType->name(),
            ]);
        }

        foreach (OrderState::cases() as $orderState) {
            ShopOrderState::query()->create([
                'id' => $orderState->value,
                'state' => $orderState->name(),
            ]);
        }

        foreach (PostageArea::cases() as $postageArea) {
            ShopPostageCountryArea::query()->create([
                'id' => $postageArea->value,
                'area' => $postageArea->name(),
                'delivery_timescale' => $postageArea->deliveryEstimate(),
            ]);
        }

        ShopPostageCountry::query()->create([
            'id' => 1,
            'postage_area_id' => PostageArea::UK->value,
            'country' => 'United Kingdom',
            'iso_code' => 'uk',
        ]);

        ShopPostageCountry::query()->create([
            'id' => 2,
            'postage_area_id' => PostageArea::EUROPE->value,
            'country' => 'France',
            'iso_code' => 'fr',
        ]);

        ShopPostageCountry::query()->create([
            'id' => 3,
            'postage_area_id' => PostageArea::AMERICA->value,
            'country' => 'America',
            'iso_code' => 'us',
        ]);

        foreach (PaymentType::cases() as $paymentType) {
            ShopPaymentType::query()->create([
                'id' => $paymentType->value,
                'type' => $paymentType->name(),
            ]);
        }

        foreach (ShippingMethod::cases() as $shippingMethod) {
            ShopShippingMethod::query()->create([
                'id' => $shippingMethod->value,
                'shipping_method' => $shippingMethod->name(),
            ]);
        }
    }
}
